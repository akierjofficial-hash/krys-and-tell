<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * If you manually add commands, list them here.
     */
    protected $commands = [
        \App\Console\Commands\CheckSeededUsers::class,
        // Optional: only add this if you want it explicitly listed.
        // Otherwise, it will still load from app/Console/Commands automatically.
        // \App\Console\Commands\SendAppointmentReminders::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ Appointment reminders (24h/1h) check
        $schedule->command('appointments:send-reminders')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Auto-load commands from app/Console/Commands
        $this->load(__DIR__.'/Commands');

        // If you use routes/console.php (Laravel default)
        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
