<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\WebPushService;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        // Only for new pending requests (adjust if your status names differ)
        if (($appointment->status ?? null) !== 'pending') return;

        app(WebPushService::class)->notifyNewBooking($appointment);
    }
}
