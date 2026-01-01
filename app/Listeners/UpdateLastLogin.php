<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Schema;

class UpdateLastLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        try {
            if (
                Schema::hasTable('users') &&
                Schema::hasColumn('users', 'last_login_at') &&
                Schema::hasColumn('users', 'last_login_ip')
            ) {
                $user->forceFill([
                    'last_login_at' => now(),
                    'last_login_ip' => request()->ip(),
                ])->saveQuietly();
            }
        } catch (\Throwable $e) {
        }
    }
}
