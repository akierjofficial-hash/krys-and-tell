<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'pwa:vapid';
    protected $description = 'Generate VAPID keys for Web Push notifications (PWA)';

    public function handle(): int
    {
        if (!class_exists(\Minishlink\WebPush\VAPID::class)) {
            $this->error('Minishlink/web-push is not installed. Run: composer install');
            return self::FAILURE;
        }

        try {
            $keys = \Minishlink\WebPush\VAPID::createVapidKeys();

            $this->line('✅ VAPID keys generated');
            $this->newLine();

            $this->info('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
            $this->info('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
            $this->newLine();

            $this->line('Optional:');
            $this->line('VAPID_SUBJECT=' . (config('app.url') ?: 'mailto:you@example.com'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to generate keys: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
