<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\ActivityLog; // ✅ add
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $role = $request->string('role')->toString(); // admin|staff|''
        $status = $request->string('status')->toString(); // active|inactive|''

        $users = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
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

        return view('admin.users.index', compact('users', 'q', 'role', 'status'));
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
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->role = $data['role'];
        $user->is_active = (bool)($data['is_active'] ?? true);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'max:72'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_active = (bool)($data['is_active'] ?? false);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function toggleActive(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', "You can't deactivate your own account.");
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'User status updated.');
    }

    // ✅ NEW: Activity Log page
    public function activity(User $user)
    {
        $logs = ActivityLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.users.activity', compact('user', 'logs'));
    }
    public function destroy(User $user)
{
    $me = auth()->user();

    // ✅ Prevent deleting yourself
    if ($me && $me->id === $user->id) {
        return back()->with('error', "You can't delete your own account.");
    }

    // ✅ Prevent deleting the last admin
    if (($user->role ?? '') === 'admin') {
        $otherAdmins = User::where('role', 'admin')
            ->where('id', '!=', $user->id)
            ->count();

        if ($otherAdmins <= 0) {
            return back()->with('error', "You can't delete the last admin account.");
        }
    }

    try {
        DB::transaction(function () use ($user) {
            // ✅ Keep appointment history: move user-linked appointments to public_email fallback
            Appointment::where('user_id', $user->id)
                ->whereNull('public_email')
                ->update(['public_email' => $user->email]);

            Appointment::where('user_id', $user->id)
                ->update(['user_id' => null]);

            // ✅ Delete user
            $user->delete();
        });

        return back()->with('success', 'User deleted successfully.');
    } catch (\Throwable $e) {
        return back()->with('error', 'Unable to delete user (may have related records). Try Deactivate instead.');
    }
}

}