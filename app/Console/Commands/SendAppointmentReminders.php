<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send appointment reminders to users (24h and 1h).';

    public function handle(): int
    {
        $now = now();

        // Safety: if reminder columns not migrated yet, don’t crash
        if (
            !Schema::hasColumn('appointments', 'reminder_24h_sent_at') ||
            !Schema::hasColumn('appointments', 'reminder_1h_sent_at') ||
            !Schema::hasColumn('appointments', 'user_id')
        ) {
            $this->warn('Reminder columns not found. Run migrations first.');
            return self::SUCCESS;
        }

        $appointments = Appointment::query()
            ->with(['user', 'service', 'doctor'])
            ->whereNotNull('user_id')
            ->where('status', 'upcoming') // ✅ only approved/upcoming
            ->whereDate('appointment_date', '>=', $now->toDateString())
            ->get();

        foreach ($appointments as $a) {
            if (!$a->user) continue;

            $start = Carbon::parse($a->appointment_date.' '.$a->appointment_time);
            $mins = $now->diffInMinutes($start, false); // negative = past

            if ($mins < 0) continue;

            // ✅ 1 hour reminder (within 60 minutes)
            if ($mins <= 60 && !$a->reminder_1h_sent_at && ($a->user->notify_1h ?? false)) {
                $a->user->notify(new AppointmentReminder($a, '1h'));
                $a->reminder_1h_sent_at = now();
                $a->save();
                continue;
            }

            // ✅ 24h reminder (within 24 hours but more than 60 mins away)
            if ($mins > 60 && $mins <= 1440 && !$a->reminder_24h_sent_at && ($a->user->notify_24h ?? false)) {
                $a->user->notify(new AppointmentReminder($a, '24h'));
                $a->reminder_24h_sent_at = now();
                $a->save();
            }
        }

        $this->info('Reminders checked.');
        return self::SUCCESS;
    }
}
