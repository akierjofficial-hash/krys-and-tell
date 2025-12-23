<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CheckSeededUsers extends Command
{
    protected $signature = 'users:check-seeds';
    protected $description = 'Check if seeded admin/staff accounts exist and if passwords match';

    public function handle(): int
    {
        $targets = [
            ['email' => 'admin@krysandtell.com', 'pass' => 'Admin123'],
            ['email' => 'staff@krysandtell.com', 'pass' => 'Staff123'],
        ];

        foreach ($targets as $t) {
            $u = User::where('email', $t['email'])->first();

            $this->line('------------------------------');
            $this->info("User: {$t['email']}");

            if (!$u) {
                $this->error('❌ NOT FOUND in database');
                continue;
            }

            $this->info('✅ FOUND');

            $ok = Hash::check($t['pass'], $u->password);
            $this->line('Password check: ' . ($ok ? '✅ MATCH' : '❌ DOES NOT MATCH'));

            // show role-ish fields if they exist (no secrets)
            $this->line('role: ' . ($u->role ?? 'n/a'));
            $this->line('user_type: ' . ($u->user_type ?? 'n/a'));
            $this->line('is_admin: ' . (isset($u->is_admin) ? (string)$u->is_admin : 'n/a'));
        }

        $this->line('------------------------------');
        return Command::SUCCESS;
    }
}
