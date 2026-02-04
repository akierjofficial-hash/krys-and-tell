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
        Log::info('AppointmentObserver: created fired', [
            'id' => $appointment->id,
            'status' => $appointment->status ?? null,
        ]);

        $send = function () use ($appointment) {
            try {
                app(WebPushService::class)->notifyNewBooking($appointment);
                Log::info('AppointmentObserver: notifyNewBooking called', ['id' => $appointment->id]);
            } catch (\Throwable $e) {
                Log::error('AppointmentObserver: push failed (ignored)', [
                    'id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        };

        // if inside DB::transaction, wait until commit
        try {
            if (DB::transactionLevel() > 0) {
                DB::afterCommit($send);
                Log::info('AppointmentObserver: scheduled afterCommit', ['id' => $appointment->id]);
                return;
            }
        } catch (\Throwable $e) {
            // ignore and send immediately
        }

        $send();
    }
}
