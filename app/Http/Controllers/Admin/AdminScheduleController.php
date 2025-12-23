<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminScheduleController extends Controller
{
    /**
     * Read-only weekly schedule page for ADMIN.
     */
    public function index()
{
    $services = Service::query()
        ->select('id', 'name', 'color')
        ->orderBy('name')
        ->get()
        ->map(function ($s) {
            // if no saved color, generate a stable fallback color
            $s->display_color = $s->color ?: $this->fallbackServiceColor($s->id, $s->name);
            return $s;
        });

    return view('admin.schedule.index', compact('services'));
}


    /**
     * FullCalendar events feed (read-only).
     * FullCalendar calls this with ?start=...&end=...
     */
    public function events(Request $request)
    {
        $start = Carbon::parse($request->get('start'));
        $end   = Carbon::parse($request->get('end'));

        $appointments = Appointment::with(['patient', 'service'])
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $events = [];

        foreach ($appointments as $a) {
            $date = Carbon::parse($a->appointment_date)->format('Y-m-d');
            $time = $a->appointment_time ?? '09:00:00';

            $startDt = Carbon::parse($date . ' ' . $time);

            $duration = (int)($a->duration_minutes ?? 60);
            if ($duration <= 0) $duration = 60;

            $endDt = $startDt->copy()->addMinutes($duration);

            $patient = trim(($a->patient?->first_name ?? '') . ' ' . ($a->patient?->last_name ?? ''));
            if ($patient === '') $patient = 'Patient';

            $serviceName = $a->service?->name ?? 'Appointment';

            // Staff forms use dentist_name. assigned_doctor exists too; fallback safely.
            $doctor = $a->dentist_name ?: ($a->assigned_doctor ?: '—');

            // ✅ Color by service (treatment)
            $serviceColor = $a->service?->color ?: $this->fallbackServiceColor($a->service_id, $serviceName);

            // Optional: override canceled items
            $status = strtolower((string)($a->status ?? ''));
            $color = $serviceColor;
            if (str_contains($status, 'cancel')) {
                $color = '#ef4444';
            }

            $events[] = [
                'title' => $patient . ' — ' . $serviceName,
                'start' => $startDt->toIso8601String(),
                'end'   => $endDt->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps' => [
                    'patient' => $patient,
                    'service' => $serviceName,
                    'doctor'  => $doctor,
                    'date_label' => $startDt->format('M d, Y'),
                    'time_label' => $startDt->format('g:i a') . ' - ' . $endDt->format('g:i a'),
                ],
            ];
        }

        return response()->json($events);
    }

    private function fallbackServiceColor($serviceId, ?string $serviceName): string
    {
        $palette = [
            '#3b82f6', // blue
            '#22c55e', // green
            '#f59e0b', // amber
            '#a855f7', // purple
            '#06b6d4', // cyan
            '#f97316', // orange
            '#84cc16', // lime
            '#14b8a6', // teal
            '#e11d48', // rose
            '#64748b', // slate
        ];

        $key = $serviceId ?: ($serviceName ?? 'service');
        $hash = crc32((string)$key);

        return $palette[$hash % count($palette)];
    }
}
