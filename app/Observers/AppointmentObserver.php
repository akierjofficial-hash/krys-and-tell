<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\WebPushService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $run = function () use ($appointment) {
            try {
                app(WebPushService::class)->notifyNewBooking($appointment);
            } catch (\Throwable $e) {
                // Never break booking flow
                Log::error('Push notify failed (ignored): ' . $e->getMessage());
            }
        };

        // If created inside a transaction, run after commit
        try {
            if (DB::transactionLevel() > 0) {
                DB::afterCommit($run);
                return;
            }
        } catch (\Throwable $e) {
            // ignore and run immediately
        }

        $run();
    }
}
