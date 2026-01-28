<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'role' => 'user', // ✅ lock role
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

        try {
            DB::transaction(function () use ($user) {

                // ✅ Prevent foreign key errors (common cause why delete "doesn't work")
                if (Schema::hasTable('appointments')) {
                    $hasUserId = Schema::hasColumn('appointments', 'user_id');
                    $hasPublicEmail = Schema::hasColumn('appointments', 'public_email');

                    if ($hasUserId) {
                        // preserve email in appointments if possible
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

                $user->delete();
            });

            return redirect()->route('admin.user_accounts.index')
                ->with('success', 'User account deleted.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.user_accounts.index')
                ->with('error', 'Delete failed (has related records). You can set the account to inactive instead.');
        }
    }
}
