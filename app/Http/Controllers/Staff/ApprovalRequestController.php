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
     * ✅ Dropdown data for the bell card (AJAX)
     */
    public function widget()
    {
        $q = Appointment::query();

        if (Schema::hasColumn('appointments', 'status')) {
            $q->where('status', 'pending');
        }

        $q->with(['service', 'doctor'])->latest();

        $pendingCount = (clone $q)->count();

        $items = $q->take(6)->get()->map(function ($a) {
            $patientName =
                trim(($a->public_first_name ?? '') . ' ' . ($a->public_last_name ?? '')) ?: ($a->public_name ?? 'N/A');

            $serviceName = $a->service->name ?? 'N/A';

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
                'date'    => $dateLabel,
                'time'    => $timeLabel,
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
            // ✅ Track previous status to avoid duplicate notifications
            $previousStatus = Schema::hasColumn('appointments', 'status') ? ($appointment->status ?? null) : null;

            DB::transaction(function () use ($appointment) {

                // ✅ Ensure patient_id exists (public booking must create/link patient)
                if (Schema::hasColumn('appointments', 'patient_id') && empty($appointment->patient_id)) {
                    $patientId = $this->findOrCreatePatientFromAppointment($appointment);

                    if (!$patientId) {
                        throw new \RuntimeException('Cannot approve: Patient record could not be created.');
                    }

                    $appointment->patient_id = $patientId;
                }

                // ✅ Update status so it appears in Payments (we included "upcoming" in payable statuses)
                if (Schema::hasColumn('appointments', 'status')) {
                    $appointment->status = 'upcoming';
                }

                $appointment->save();
            });

            // ✅ Notify user (only on transition to upcoming)
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

            // ✅ AJAX response
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
            // ✅ Track previous status to avoid duplicate notifications
            $previousStatus = Schema::hasColumn('appointments', 'status') ? ($appointment->status ?? null) : null;

            if (Schema::hasColumn('appointments', 'status')) {
                $appointment->status = 'declined';
            }

            $appointment->save();

            // ✅ Notify user (only on transition to declined)
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

        // ✅ pull possible gender/birthdate if present on appointment
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

        // ✅ Save gender/birthdate IF your patients table has them
        if ($gender && Schema::hasColumn('patients', 'gender') && empty($patient->gender)) {
            $patient->gender = $gender;
        }

        if ($birthdate && Schema::hasColumn('patients', 'birthdate') && empty($patient->birthdate)) {
            $patient->birthdate = $birthdate;
        }

        /**
         * ✅ IMPORTANT: If patients.birthdate is NOT NULL and you removed it from booking,
         * patient creation will FAIL. To prevent that, we set a safe fallback only when needed.
         *
         * Better: make birthdate nullable in migration.
         * But for now, this ensures approval won't break.
         */
        if (Schema::hasColumn('patients', 'birthdate') && empty($patient->birthdate)) {
            // fallback to Jan 1 2000 (or change to your preference)
            $patient->birthdate = '2000-01-01';
        }

        $patient->save();

        return $patient->id;
    }
}
