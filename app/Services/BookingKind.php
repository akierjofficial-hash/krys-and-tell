<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;

class BookingKind
{
    public static function isWalkInService(?Service $service): bool
    {
        if (!$service) return false;
        if ($service->is_walk_in) return true;
        $duration = $service->duration_minutes;
        if ($duration === null || $duration === '') return true;
        return is_numeric($duration) && (int) $duration > 0 && (int) $duration <= 5;
    }

    public static function isWalkIn(Appointment $appointment): bool
    {
        return (bool) $appointment->is_walk_in_request || self::isWalkInService($appointment->service);
    }

    public static function origin(Appointment $appointment): string
    {
        return $appointment->user_id || $appointment->public_name || $appointment->public_first_name
            ? 'Website request' : 'Staff-created appointment';
    }
}
