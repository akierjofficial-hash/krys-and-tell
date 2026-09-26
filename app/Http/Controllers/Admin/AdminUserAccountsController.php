<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\AdminAuditService;

class AdminUserAccountsController extends Controller
{
    private function usersQuery()
    {
        return User::query()->where('role', 'user');
    }

    private function ensureIsUser(User $user): void
    {
        $role = strtolower((string)($user->role ?? ''));
        if ($role !== 'user') {
            abort(404);
        }
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $status = (string)$request->query('status', '');

        $query = $this->usersQuery()->withCount('appointments');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $hasActive = Schema::hasColumn((new User)->getTable(), 'is_active');
        if ($hasActive && $status === 'active') $query->where('is_active', 1);
        if ($hasActive && $status === 'inactive') $query->where('is_active', 0);

        $users = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $emails = $users->getCollection()->pluck('email')->filter()->map(fn ($email) => mb_strtolower($email))->all();
        $patientsByEmail = Patient::whereIn(DB::raw('LOWER(email)'), $emails)->get()->groupBy(fn ($patient) => mb_strtolower($patient->email));
        $users->getCollection()->each(function ($account) use ($patientsByEmail) {
            $matches = $patientsByEmail->get(mb_strtolower($account->email), collect());
            $account->setRelation('linkedPatient', $matches->count() === 1 ? $matches->first() : null);
        });

        return view('admin.user_accounts.index', compact('users', 'q', 'status', 'hasActive'));
    }

    public function edit(User $user)
    {
        $this->ensureIsUser($user);

        $hasActive = Schema::hasColumn((new User)->getTable(), 'is_active');
        return view('admin.user_accounts.edit', compact('user', 'hasActive'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureIsUser($user);

        $hasActive = Schema::hasColumn((new User)->getTable(), 'is_active');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => [$hasActive ? 'sometimes' : 'nullable', 'boolean'],
            'establish_local_password' => ['nullable', 'boolean'],
        ]);

        if (!empty($data['password']) && $user->google_id && !$request->boolean('establish_local_password')) {
            throw ValidationException::withMessages(['password' => 'Confirm that you want to establish a local password for this Google-connected account.']);
        }

        $before = $user->only(['name', 'email', 'is_active', 'password_set']);

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => 'user', // ✅ lock role
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->password_set = true;
        }

        if ($hasActive) {
            $user->is_active = $request->boolean('is_active', true);
        }

        $user->save();
        app(AdminAuditService::class)->record($request->user(), 'website_account.updated', $user, 'Website account updated.', $before,
            $user->only(['name', 'email', 'is_active', 'password_set']), null, !empty($data['password']));

        return $this->ktRedirectToReturn($request, 'admin.user_accounts.index')
            ->with('success', 'User account updated.');
    }

    public function restore(Request $request, int $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->ensureIsUser($user);

        $user->restore();
        app(AdminAuditService::class)->record($request->user(), 'website_account.restored', $user, 'Website account restored.');

        return $this->ktRedirectToReturn($request, 'admin.user_accounts.index')
            ->with('success', 'User account restored successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->ensureIsUser($user);

        try {
            DB::transaction(function () use ($user) {

                // ✅ Prevent foreign key issues if appointments link to user_id
                if (Schema::hasTable('appointments')) {
                    $hasUserId = Schema::hasColumn('appointments', 'user_id');
                    $hasPublicEmail = Schema::hasColumn('appointments', 'public_email');

                    if ($hasUserId) {
                        if ($hasPublicEmail) {
                            DB::table('appointments')
                                ->where('user_id', $user->id)
                                ->whereNull('public_email')
                                ->update(['public_email' => $user->email]);
                        }

                        DB::table('appointments')
                            ->where('user_id', $user->id)
                            ->update(['user_id' => null]);
                    }
                }

                $before = $user->only(['name', 'email', 'is_active']);
                $user->delete();
                app(AdminAuditService::class)->record(request()->user(), 'website_account.deleted', $user, 'Website account moved to Deleted Accounts.', $before, [], null, true);
            });

            $returnUrl = $this->ktReturnUrl($request, 'admin.user_accounts.index');

            return $this->ktRedirectToReturn($request, 'admin.user_accounts.index')
                ->with('success', 'User account deleted.')
                ->with('undo', [
                    'message' => 'User account deleted: ' . (($user->name ?? $user->email) ?: ('#'.$user->id)),
                    'url' => route('admin.user_accounts.restore', ['id' => $user->id, 'return' => $returnUrl]),
                    'ms' => 10000,
                ]);
        } catch (\Throwable $e) {
            return $this->ktRedirectToReturn($request, 'admin.user_accounts.index')
                ->with('error', 'Delete failed (has related records). You can set the account to inactive instead.');
        }
    }
}
