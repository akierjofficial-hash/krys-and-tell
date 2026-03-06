<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CheckSeededUsers extends Command
{
    protected $signature = 'users:check-seeds';
    protected $description = 'Check if configured seeded admin/staff accounts exist';

    public function handle(): int
    {
        $adminEmail = trim((string) env('SEED_ADMIN_EMAIL', 'admin@krysandtell.com'));
        $staffEmail = trim((string) env('SEED_STAFF_EMAIL', 'staff@krysandtell.com'));
        $adminPass = (string) env('SEED_ADMIN_PASSWORD', '');
        $staffPass = (string) env('SEED_STAFF_PASSWORD', '');

        $targets = [
            ['email' => $adminEmail, 'pass' => $adminPass],
            ['email' => $staffEmail, 'pass' => $staffPass],
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

            if ($t['pass'] !== '') {
                $ok = Hash::check($t['pass'], $u->password);
                $this->line('Password check: ' . ($ok ? '✅ MATCH' : '❌ DOES NOT MATCH'));
            } else {
                $this->line('Password check: skipped (SEED_*_PASSWORD not set)');
            }

            // show role-ish fields if they exist (no secrets)
            $this->line('role: ' . ($u->role ?? 'n/a'));
            $this->line('user_type: ' . ($u->user_type ?? 'n/a'));
            $this->line('is_admin: ' . (isset($u->is_admin) ? (string)$u->is_admin : 'n/a'));
        }

        $this->line('------------------------------');
        return Command::SUCCESS;
    }
}
