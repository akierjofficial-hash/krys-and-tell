<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\AdminAccountService;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    private const MANAGEABLE_ROLES = ['admin', 'staff'];

    public function __construct(
        private AdminAccountService $accounts,
        private AdminAuditService $audit,
    ) {}

    private function ensureManageableUser(User $user): void
    {
        $role = strtolower((string) ($user->role ?? ''));

        if (!in_array($role, self::MANAGEABLE_ROLES, true)) {
            abort(404);
        }
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $role = $request->string('role')->toString();     // admin|staff|''
        $status = $request->string('status')->toString(); // active|inactive|''

        // ✅ If role filter is invalid, ignore it
        if (!in_array($role, self::MANAGEABLE_ROLES, true)) {
            $role = '';
        }

        $users = User::query()
            ->whereIn('role', self::MANAGEABLE_ROLES)
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->whereLike('name', "%{$q}%")
                        ->orWhereLike('email', "%{$q}%");
                });
            })
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($status, function ($query) use ($status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $activeAdminCount = User::where('role', 'admin')->where('is_active', true)->count();

        return view('admin.users.index', compact('users', 'q', 'role', 'status', 'activeAdminCount'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'role' => ['required', Rule::in(self::MANAGEABLE_ROLES)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->role = $data['role'];
        $user->is_active = (bool)($data['is_active'] ?? true);
        $user->save();

        $this->audit->record($request->user(), 'account.created', $user, 'Staff or administrator account created.', [],
            $user->only(['name', 'email', 'role', 'is_active']));

        return $this->ktRedirectToReturn($request, 'admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->ensureManageableUser($user);

        $soleActiveAdmin = $this->accounts->soleActiveAdmin($user);
        $isSelf = auth()->id() === $user->id;

        return view('admin.users.edit', compact('user', 'soleActiveAdmin', 'isSelf'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureManageableUser($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(self::MANAGEABLE_ROLES)],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'max:72'],
            'current_password' => ['nullable', 'string', 'max:72'],
            'admin_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->accounts->update($request->user(), $user, $data);

        return $this->ktRedirectToReturn($request, 'admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function toggleActive(Request $request, User $user)
    {
        $this->ensureManageableUser($user);

        $data = $request->validate([
            'current_password' => ['nullable', 'string', 'max:72'],
            'admin_reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->accounts->toggle($request->user(), $user, $data);

        return $this->ktRedirectToReturn($request, 'admin.users.index')
            ->with('success', 'User status updated.');
    }

    public function activity(User $user)
    {
        $this->ensureManageableUser($user);

        $logs = ActivityLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.users.activity', compact('user', 'logs'));
    }

    public function restore(Request $request, int $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->ensureManageableUser($user);
        $this->accounts->restore($request->user(), $id);

        return $this->ktRedirectToReturn($request, 'admin.users.index')
            ->with('success', 'User restored successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->ensureManageableUser($user);

        $data = $request->validate([
            'current_password' => ['required', 'string', 'max:72'],
            'admin_reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->accounts->delete($request->user(), $user, $data);

            $returnUrl = $this->ktReturnUrl($request, 'admin.users.index');

            return $this->ktRedirectToReturn($request, 'admin.users.index')
                ->with('success', 'User deleted successfully.')
                ->with('undo', [
                    'message' => 'User deleted: ' . (($user->name ?? $user->email) ?: ('#'.$user->id)),
                    'url' => route('admin.users.restore', ['id' => $user->id, 'return' => $returnUrl]),
                    'ms' => 10000,
                ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->ktRedirectToReturn($request, 'admin.users.index')
                ->with('error', 'Unable to delete user (may have related records). Try Deactivate instead.');
        }
    }
}
