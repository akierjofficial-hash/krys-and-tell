<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $doctors = Appointment::query()
            ->select('dentist_name')
            ->whereNotNull('dentist_name')
            ->where('dentist_name', '!=', '')
            ->distinct()
            ->orderBy('dentist_name')
            ->pluck('dentist_name');

        $statuses = Appointment::query()
            ->select('status')
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        // Main query (read-only)
        $query = Appointment::query()
            ->with(['patient', 'service'])
            ->when($search !== '', function ($q) use ($search) {
                // Group OR search terms so they don't escape other filters (doctor/service/status)
                $q->where(function ($w) use ($search) {
                    $w->whereHas('patient', function ($p) use ($search) {
                        $p->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere(DB::raw("CONCAT(first_name,' ',last_name)"), 'like', "%{$search}%");
                    })
                    ->orWhere('dentist_name', 'like', "%{$search}%");
                });
            })
            ->when($doctor !== '', fn ($q) => $q->where('dentist_name', $doctor))
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
