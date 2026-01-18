<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Notifications\AppointmentApproved;
use App\Notifications\AppointmentDeclined;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class ApprovalRequestController extends Controller
{
    public function index()
    {
        $q = Appointment::query();

        if (Schema::hasColumn('appointments', 'status')) {
            $q->where('status', 'pending');
        }

        $q->with(['service', 'doctor'])->latest();

        $requests = $q->paginate(12);

        return view('staff.approvals.index', compact('requests'));
    }

    /**
     * ✅ Live dropdown/widget data (AJAX polling) — returns JSON
     * Supports ?limit=6 (default) up to 20
     */
    public function widget(Request $request)
    {
        $limit = (int) $request->query('limit', 6);
        $limit = max(1, min(20, $limit));

        $q = Appointment::query();

        if (Schema::hasColumn('appointments', 'status')) {
            $q->where('status', 'pending');
        }

        $q->with(['service', 'doctor'])->latest();

        $pendingCount = (clone $q)->count();

        $items = (clone $q)->take($limit)->get()->map(function ($a) {
            $patientName =
                trim(($a->public_first_name ?? '') . ' ' . ($a->public_last_name ?? '')) ?: ($a->public_name ?? 'N/A');

            $serviceName = $a->service->name ?? 'N/A';
            $doctorName  = $a->doctor->name ?? ($a->dentist_name ?? '—');

            $dateLabel = '—';
            $timeLabel = '—';

            try {
                if (!empty($a->appointment_date)) {
                    $dateLabel = Carbon::parse($a->appointment_date)->format('M d, Y');
                }
                if (!empty($a->appointment_time)) {
                    $timeLabel = Carbon::parse($a->appointment_time)->format('h:i A');
                }
            } catch (\Throwable $e) {
                // keep —
            }

            return [
                'id'      => $a->id,
                'patient' => $patientName,
                'service' => $serviceName,
                'doctor'  => $doctorName,

                'email'   => $a->public_email ?? '—',
                'phone'   => $a->public_phone ?? '—',
                'address' => $a->public_address ?? '—',

                'date'    => $dateLabel,
                'time'    => $timeLabel,

                // handy URLs so frontend can build buttons
                'approve_url' => route('staff.approvals.approve', $a),
                'decline_url' => route('staff.approvals.decline', $a),
                'index_url'   => route('staff.approvals.index'),
            ];
        })->values();

        return response()->json([
            'pendingCount' => $pendingCount,
            'items'        => $items,
        ]);
    }

    public function approve(Request $request, Appointment $appointment)
    {
        try {
            $previousStatus = Schema::hasColumn('appointments', 'status') ? ($appointment->status ?? null) : null;

            DB::transaction(function () use ($appointment) {

                if (Schema::hasColumn('appointments', 'patient_id') && empty($appointment->patient_id)) {
                    $patientId = $this->findOrCreatePatientFromAppointment($appointment);

                    if (!$patientId) {
                        throw new \RuntimeException('Cannot approve: Patient record could not be created.');
                    }

                    $appointment->patient_id = $patientId;
                }

                if (Schema::hasColumn('appointments', 'status')) {
                    $appointment->status = 'upcoming';
                }

                $appointment->save();
            });

            $appointment->refresh()->loadMissing(['user', 'service', 'doctor']);

            if (
                Schema::hasColumn('appointments', 'status') &&
                $previousStatus !== 'upcoming' &&
                ($appointment->status ?? null) === 'upcoming'
            ) {
                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentApproved($appointment));
                } elseif (!empty($appointment->public_email)) {
                    Notification::route('mail', $appointment->public_email)
                        ->notify(new AppointmentApproved($appointment));
                }
            }

            if ($request->expectsJson()) {
                $pendingCount = Schema::hasColumn('appointments', 'status')
                    ? Appointment::where('status', 'pending')->count()
                    : 0;

                return response()->json([
                    'ok'           => true,
                    'message'      => 'Booking approved.',
                    'pendingCount' => $pendingCount,
                ]);
            }

            return redirect()->route('staff.approvals.index')->with('success', 'Booking approved.');
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'      => false,
                    'message' => $e->getMessage() ?: 'Approval failed.',
                ], 422);
            }

            return redirect()->route('staff.approvals.index')->with('error', $e->getMessage() ?: 'Approval failed.');
        }
    }

    public function decline(Request $request, Appointment $appointment)
    {
        try {
            $previousStatus = Schema::hasColumn('appointments', 'status') ? ($appointment->status ?? null) : null;

            if (Schema::hasColumn('appointments', 'status')) {
                $appointment->status = 'declined';
            }

            $appointment->save();

            $appointment->refresh()->loadMissing(['user', 'service', 'doctor']);

            if (
                Schema::hasColumn('appointments', 'status') &&
                $previousStatus !== 'declined' &&
                ($appointment->status ?? null) === 'declined'
            ) {
                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentDeclined($appointment));
                } elseif (!empty($appointment->public_email)) {
                    Notification::route('mail', $appointment->public_email)
                        ->notify(new AppointmentDeclined($appointment));
                }
            }

            if ($request->expectsJson()) {
                $pendingCount = Schema::hasColumn('appointments', 'status')
                    ? Appointment::where('status', 'pending')->count()
                    : 0;

                return response()->json([
                    'ok'           => true,
                    'message'      => 'Booking declined.',
                    'pendingCount' => $pendingCount,
                ]);
            }

            return redirect()->route('staff.approvals.index')->with('success', 'Booking declined.');
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'      => false,
                    'message' => $e->getMessage() ?: 'Decline failed.',
                ], 422);
            }

            return redirect()->route('staff.approvals.index')->with('error', 'Decline failed.');
        }
    }

    private function findOrCreatePatientFromAppointment(Appointment $a): ?int
    {
        if (!Schema::hasTable('patients')) return null;

        $first   = $a->public_first_name ?? null;
        $middle  = $a->public_middle_name ?? null;
        $last    = $a->public_last_name ?? null;
        $email   = $a->public_email ?? null;
        $phone   = $a->public_phone ?? null;
        $address = $a->public_address ?? null;

        $gender    = $a->public_gender ?? ($a->gender ?? null);
        $birthdate = $a->public_birthdate ?? ($a->birthdate ?? null);

        $patient = null;

        if ($email && Schema::hasColumn('patients', 'email')) {
            $patient = Patient::where('email', $email)->first();
        }

        if (!$patient && $phone) {
            foreach (['contact', 'contact_number', 'phone', 'mobile'] as $col) {
                if (Schema::hasColumn('patients', $col)) {
                    $patient = Patient::where($col, $phone)->first();
                    if ($patient) break;
                }
            }
        }

        if (
            !$patient && $first && $last &&
            Schema::hasColumn('patients', 'first_name') &&
            Schema::hasColumn('patients', 'last_name')
        ) {
            $patient = Patient::whereRaw('LOWER(first_name)=? AND LOWER(last_name)=?', [
                mb_strtolower($first),
                mb_strtolower($last),
            ])->first();
        }

        $patient = $patient ?: new Patient();

        if ($first && Schema::hasColumn('patients', 'first_name')) $patient->first_name = $first;
        if ($last  && Schema::hasColumn('patients', 'last_name'))  $patient->last_name  = $last;

        if ($middle && Schema::hasColumn('patients', 'middle_name') && empty($patient->middle_name)) {
            $patient->middle_name = $middle;
        }

        if (Schema::hasColumn('patients', 'name') && empty($patient->name)) {
            $patient->name = trim(($first ?: '') . ' ' . ($middle ? $middle . ' ' : '') . ($last ?: ''));
        }

        if ($email && Schema::hasColumn('patients', 'email') && empty($patient->email)) {
            $patient->email = $email;
        }

        if ($phone) {
            foreach (['contact', 'contact_number', 'phone', 'mobile'] as $col) {
                if (Schema::hasColumn('patients', $col) && empty($patient->{$col})) {
                    $patient->{$col} = $phone;
                    break;
                }
            }
        }

        if ($address && Schema::hasColumn('patients', 'address') && empty($patient->address)) {
            $patient->address = $address;
        }

        if ($gender && Schema::hasColumn('patients', 'gender') && empty($patient->gender)) {
            $patient->gender = $gender;
        }

        if ($birthdate && Schema::hasColumn('patients', 'birthdate') && empty($patient->birthdate)) {
            $patient->birthdate = $birthdate;
        }

        // fallback to prevent approval failing if birthdate is NOT NULL
        if (Schema::hasColumn('patients', 'birthdate') && empty($patient->birthdate)) {
            $patient->birthdate = '2000-01-01';
        }

        $patient->save();

        return $patient->id;
    }
}
