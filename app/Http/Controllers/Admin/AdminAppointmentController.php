<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search   = trim((string) $request->get('q', ''));
        $doctor   = trim((string) $request->get('doctor', ''));
        $serviceId = $request->get('service_id');
        $status   = trim((string) $request->get('status', ''));

        // Dropdown options
        $services = Service::query()
            ->select('id', 'name', 'color')
            ->orderBy('name')
            ->get();

        // Dentist/doctor stored as string on appointments
        $legacyDoctors = Appointment::query()
            ->select('dentist_name')
            ->whereNotNull('dentist_name')
            ->where('dentist_name', '!=', '')
            ->distinct()
            ->orderBy('dentist_name')
            ->pluck('dentist_name');
        $linkedDoctors = Doctor::query()->whereIn('id', Appointment::query()
            ->whereNotNull('doctor_id')->select('doctor_id'))->pluck('name');
        $doctors = $legacyDoctors->merge($linkedDoctors)->unique()->sort()->values();

        $statuses = Appointment::query()
            ->select('status')
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        // Main query (read-only)
        $query = Appointment::query()
            ->with(['patient', 'service', 'doctor'])
            ->when($search !== '', function ($q) use ($search) {
                // Group OR search terms so they don't escape other filters (doctor/service/status)
                $q->where(function ($w) use ($search) {
                    $parts = preg_split('/[\s,]+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $w->whereHas('patient', function ($p) use ($parts) {
                        foreach ($parts as $part) {
                            $p->where(fn ($name) => $name->whereLike('first_name', "%{$part}%")
                                ->orWhereLike('last_name', "%{$part}%")
                                ->orWhereLike('middle_name', "%{$part}%"));
                        }
                    })->orWhere(function ($public) use ($parts) {
                        foreach ($parts as $part) {
                            $public->where(fn ($name) => $name->whereLike('public_first_name', "%{$part}%")
                                ->orWhereLike('public_middle_name', "%{$part}%")
                                ->orWhereLike('public_last_name', "%{$part}%")
                                ->orWhereLike('public_name', "%{$part}%"));
                        }
                    })
                    ->orWhereLike('dentist_name', "%{$search}%")
                    ->orWhereHas('doctor', fn ($doctor) => $doctor->whereLike('name', "%{$search}%"));
                });
            })
            ->when($doctor !== '', fn ($q) => $q->where(fn ($matching) => $matching
                ->where('dentist_name', $doctor)
                ->orWhereHas('doctor', fn ($linked) => $linked->where('name', $doctor))))
            ->when($serviceId !== null && $serviceId !== '' && $serviceId !== 'all', fn ($q) => $q->where('service_id', $serviceId))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc');

        $appointments = $query->paginate(15)->withQueryString();

        // Precompute end time label if duration exists
        $appointments->getCollection()->transform(function ($a) {
            $date = $a->appointment_date ? Carbon::parse($a->appointment_date)->format('Y-m-d') : null;
            $time = $a->appointment_time ?: null;

            $a->time_label = $time ? Carbon::parse(($date ?: date('Y-m-d')) . ' ' . $time)->format('g:i a') : '—';

            if ($date && $time) {
                $start = Carbon::parse($date . ' ' . $time);
                $mins = (int)($a->duration_minutes ?? 0);
                if ($mins > 0) {
                    $a->end_time_label = $start->copy()->addMinutes($mins)->format('g:i a');
                } else {
                    $a->end_time_label = null;
                }
            } else {
                $a->end_time_label = null;
            }

            return $a;
        });

        return view('admin.appointments.index', compact(
            'appointments',
            'services',
            'doctors',
            'statuses',
            'search',
            'doctor',
            'serviceId',
            'status'
        ));
    }

    // same palette generator used on schedule (for procedure pill colors)
    public static function fallbackServiceColor($serviceId, ?string $serviceName): string
    {
        $palette = [
            '#3b82f6', '#22c55e', '#f59e0b', '#a855f7', '#06b6d4',
            '#f97316', '#84cc16', '#14b8a6', '#e11d48', '#64748b',
        ];

        $key = $serviceId ?: ($serviceName ?? 'service');
        $hash = crc32((string)$key);

        return $palette[$hash % count($palette)];
    }
}
