<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog; // ✅ add
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
}
