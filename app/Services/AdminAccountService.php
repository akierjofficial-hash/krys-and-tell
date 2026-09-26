<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAccountService
{
    public function __construct(private AdminAuditService $audit) {}

    public function update(User $actor, User $account, array $data): User
    {
        return DB::transaction(function () use ($actor, $account, $data) {
            $account = User::withTrashed()->lockForUpdate()->findOrFail($account->id);
            $before = $account->only(['name', 'email', 'role', 'is_active']);
            $newRole = $data['role'];
            $newActive = (bool) ($data['is_active'] ?? false);
            $roleChanged = $newRole !== $account->role;
            $passwordChanged = !empty($data['password']);
            $adminDeactivated = $account->role === 'admin' && $account->is_active && !$newActive;

            if ($actor->is($account) && ($newRole !== 'admin' || !$newActive)) {
                throw ValidationException::withMessages(['role' => 'You cannot demote or deactivate your own administrator account.']);
            }

            if ($roleChanged || $passwordChanged || $adminDeactivated) {
                $this->verifyCurrentPassword($actor, $data['current_password'] ?? null);
                $this->requireReason($data['admin_reason'] ?? null);
            }

            if ($account->role === 'admin' && ($newRole !== 'admin' || !$newActive)) {
                $this->assertAnotherActiveAdmin($account);
            }

            $account->forceFill([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $newRole,
                'is_active' => $newActive,
            ]);
            if ($passwordChanged) $account->password = $data['password'];
            $account->save();

            $this->audit->record($actor, 'account.updated', $account, 'Staff or administrator account updated.', $before,
                $account->only(['name', 'email', 'role', 'is_active']), $data['admin_reason'] ?? null,
                $roleChanged || $passwordChanged || $adminDeactivated);
            return $account;
        });
    }

    public function toggle(User $actor, User $account, array $data): User
    {
        return DB::transaction(function () use ($actor, $account, $data) {
            $account = User::lockForUpdate()->findOrFail($account->id);
            if ($actor->is($account)) {
                throw ValidationException::withMessages(['account' => 'You cannot deactivate your own administrator account.']);
            }
            $deactivatingAdmin = $account->role === 'admin' && $account->is_active;
            if ($deactivatingAdmin) {
                $this->verifyCurrentPassword($actor, $data['current_password'] ?? null);
                $this->requireReason($data['admin_reason'] ?? null);
                $this->assertAnotherActiveAdmin($account);
            }
            $before = ['is_active' => (bool) $account->is_active];
            $account->is_active = !$account->is_active;
            $account->save();
            $this->audit->record($actor, 'account.status_changed', $account, 'Account status changed.', $before,
                ['is_active' => (bool) $account->is_active], $data['admin_reason'] ?? null, $deactivatingAdmin);
            return $account;
        });
    }

    public function delete(User $actor, User $account, array $data): void
    {
        DB::transaction(function () use ($actor, $account, $data) {
            $account = User::lockForUpdate()->findOrFail($account->id);
            if ($actor->is($account)) {
                throw ValidationException::withMessages(['account' => 'You cannot delete your own administrator account.']);
            }
            $this->verifyCurrentPassword($actor, $data['current_password'] ?? null);
            $this->requireReason($data['admin_reason'] ?? null);
            if ($account->role === 'admin') $this->assertAnotherActiveAdmin($account);

            Appointment::where('user_id', $account->id)->whereNull('public_email')->update(['public_email' => $account->email]);
            Appointment::where('user_id', $account->id)->update(['user_id' => null]);
            $before = $account->only(['name', 'email', 'role', 'is_active']);
            $account->delete();
            $this->audit->record($actor, 'account.deleted', $account, 'Account moved to Deleted Accounts.', $before, [],
                $data['admin_reason'], true);
        });
    }

    public function restore(User $actor, int $id): User
    {
        return DB::transaction(function () use ($actor, $id) {
            $account = User::withTrashed()->lockForUpdate()->findOrFail($id);
            $account->restore();
            $this->audit->record($actor, 'account.restored', $account, 'Deleted account restored.', [],
                $account->only(['name', 'email', 'role', 'is_active']));
            return $account;
        });
    }

    public function soleActiveAdmin(User $account): bool
    {
        return $account->role === 'admin' && $account->is_active
            && User::where('role', 'admin')->where('is_active', true)->count() === 1;
    }

    private function assertAnotherActiveAdmin(User $account): void
    {
        User::where('role', 'admin')->where('is_active', true)->lockForUpdate()->get();
        if (!User::where('role', 'admin')->where('is_active', true)->whereKeyNot($account->id)->exists()) {
            throw ValidationException::withMessages(['role' => 'The last active administrator cannot be demoted, deactivated, or deleted.']);
        }
    }

    private function verifyCurrentPassword(User $actor, ?string $password): void
    {
        if (!$password || !Hash::check($password, $actor->password)) {
            throw ValidationException::withMessages(['current_password' => 'Enter your current password to authorize this change.']);
        }
    }

    private function requireReason(?string $reason): void
    {
        if (!is_string($reason) || trim($reason) === '') {
            throw ValidationException::withMessages(['admin_reason' => 'Enter an administrative reason for this sensitive action.']);
        }
    }
}
