<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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

        $query = $this->usersQuery();

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

        return view('admin.user_accounts.index', compact('users', 'q', 'status', 'hasActive'));
    }

    public function create()
    {
        $hasActive = Schema::hasColumn((new User)->getTable(), 'is_active');
        return view('admin.user_accounts.create', compact('hasActive'));
    }

    public function store(Request $request)
    {
        $hasActive = Schema::hasColumn((new User)->getTable(), 'is_active');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => [$hasActive ? 'sometimes' : 'nullable', 'boolean'],
        ]);

        $user = new User();
        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        if ($hasActive) {
            $user->is_active = $request->boolean('is_active', true);
        }

        $user->save();

        return redirect()->route('admin.user_accounts.index')
            ->with('success', 'User account created.');
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
        ]);

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => 'user', // keep it locked
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if ($hasActive) {
            $user->is_active = $request->boolean('is_active', true);
        }

        $user->save();

        return redirect()->route('admin.user_accounts.index')
            ->with('success', 'User account updated.');
    }

    public function destroy(User $user)
    {
        $this->ensureIsUser($user);

        $user->delete();

        return redirect()->route('admin.user_accounts.index')
            ->with('success', 'User account deleted.');
    }
}
