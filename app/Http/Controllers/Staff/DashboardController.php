<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\StaffDashboardService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(StaffDashboardService $dashboard): View
    {
        return view('staff.dashboard.index', $dashboard->data());
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);
        $timezone = config('app.timezone');
        $start = CarbonImmutable::parse($validated['start'])->setTimezone($timezone)->toDateString();
        $end = CarbonImmutable::parse($validated['end'])->setTimezone($timezone)->subDay()->toDateString();

        $appointments = Appointment::query()
            ->dashboardActive()
            ->whereBetween('appointment_date', [$start, $end])
            ->with(['patient:id,first_name,last_name', 'service:id,name', 'doctor:id,name'])
            ->orderBy('appointment_date')
            ->orderByRaw('CASE WHEN appointment_time IS NULL THEN 1 ELSE 0 END')
            ->orderBy('appointment_time')
            ->get();

        $events = $appointments->map(function (Appointment $appointment) use ($timezone) {
            $status = strtolower((string) $appointment->status);
            $color = match (true) {
                $status === 'pending' => '#d97706',
                $status === 'walked_in' => '#7c3aed',
                default => '#0d6efd',
            };
            $date = CarbonImmutable::parse($appointment->appointment_date, $timezone)->toDateString();
            $untimed = empty($appointment->appointment_time);
            $duration = max(15, (int) ($appointment->duration_minutes ?: 60));
            $title = $appointment->displayPatientName() . ' — ' . ($appointment->service?->name ?: 'Appointment');

            $event = [
                'id' => (string) $appointment->id,
                'title' => $untimed ? 'Walk-in · ' . $title : $title,
                'allDay' => $untimed,
                'start' => $untimed
                    ? $date
                    : CarbonImmutable::parse($date . ' ' . $appointment->appointment_time, $timezone)->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'status' => $status,
                    'url' => route('staff.appointments.show', ['appointment' => $appointment->id]),
                ],
            ];

            if (!$untimed) {
                $event['end'] = CarbonImmutable::parse($date . ' ' . $appointment->appointment_time, $timezone)
                    ->addMinutes($duration)
                    ->toIso8601String();
            }

            return $event;
        });

        return response()->json($events);
    }
}
