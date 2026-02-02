<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use App\Notifications\AppointmentApproved;
use App\Notifications\AppointmentDeclined;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class AdminApprovalRequestController extends Controller
{
    // Match the public slot rules
    private const CLINIC_OPEN  = '09:00';
    private const CLINIC_CLOSE = '17:00'; // last start slot is 16:00
    private const SLOT_MINUTES = 60;      // 1 hour blocks
    private const CHAIRS       = 2;       // 2 chairs = 2 patients per hour
    private const LEAD_MINUTES_TODAY = 60;

    public function index()
    {
        $q = Appointment::query();

        if (Schema::hasColumn('appointments', 'status')) {
            $q->where('status', 'pending');
        }

        $q->with(['service', 'doctor'])->latest();

        $requests = $q->paginate(12);

        // ✅ Needed for Edit & Approve modal
        $doctors = $this->activeDoctors();
        $doctorRequired = $this->doctorRequired();

        return view('admin.approvals.index', compact('requests', 'doctors', 'doctorRequired'));
    }

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
            $dateRaw = null;
            $timeRaw = null;

            try {
                if (!empty($a->appointment_date)) {
                    $dateRaw = Carbon::parse($a->appointment_date)->toDateString();
                    $dateLabel = Carbon::parse($a->appointment_date)->format('M d, Y');
                }
                if (!empty($a->appointment_time)) {
                    $timeRaw = Carbon::parse($a->appointment_time)->format('H:i');
                    $timeLabel = Carbon::parse($a->appointment_time)->format('h:i A');
                }
            } catch (\Throwable $e) {}

            $doctorId = null;
            if (Schema::hasColumn('appointments', 'doctor_id')) {
                $doctorId = $a->doctor_id ?? null;
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

                // ✅ raw values needed for modal prefill
                'service_id' => $a->service_id ?? null,
                'doctor_id'  => $doctorId,
                'date_raw'   => $dateRaw,
                'time_raw'   => $timeRaw,

                'approve_url' => route('admin.approvals.approve', $a),
                'decline_url' => route('admin.approvals.decline', $a),
                'index_url'   => route('admin.approvals.index'),
            ];
        })->values();

        return response()->json([
            'pendingCount' => $pendingCount,
            'items'        => $items,
        ]);
    }

    /**
     * ✅ Approve booking
     * Supports staff/admin edits before approving:
     * - doctor_id
     * - appointment_date (Y-m-d)
     * - appointment_time (H:i)
     * - staff_note (reason/notes)
     */
    public function approve(Request $request, Appointment $appointment)
    {
        try {
            $previousStatus = Schema::hasColumn('appointments', 'status') ? ($appointment->status ?? null) : null;

            $appointment->loadMissing(['user', 'service', 'doctor']);

            $doctorRequired = $this->doctorRequired();

            $service = $appointment->service;
            if (!$service && Schema::hasColumn('appointments', 'service_id') && !empty($appointment->service_id) && class_exists(Service::class)) {
                $service = Service::find($appointment->service_id);
            }
            $isWalkIn = ($service instanceof Service) ? $this->isWalkIn($service) : false;

            // detect changes to require a reason
            $incomingDoctorId = $request->has('doctor_id')
                ? ($request->filled('doctor_id') ? (int) $request->doctor_id : null)
                : (Schema::hasColumn('appointments', 'doctor_id') ? ($appointment->doctor_id ?? null) : null);

            $incomingDate = $request->filled('appointment_date')
                ? Carbon::parse($request->appointment_date)->toDateString()
                : ($appointment->appointment_date ?? null);

            $incomingTime = null;
            if (!$isWalkIn && Schema::hasColumn('appointments', 'appointment_time')) {
                if ($request->filled('appointment_time')) {
                    $incomingTime = Carbon::createFromFormat('H:i', $request->appointment_time)->format('H:i');
                } elseif (!empty($appointment->appointment_time)) {
                    $incomingTime = Carbon::parse($appointment->appointment_time)->format('H:i');
                }
            }

            $didChangeDoctor = Schema::hasColumn('appointments', 'doctor_id')
                ? ((string)($appointment->doctor_id ?? '') !== (string)($incomingDoctorId ?? ''))
                : false;

            $didChangeDate = Schema::hasColumn('appointments', 'appointment_date')
                ? ((string)($appointment->appointment_date ?? '') !== (string)($incomingDate ?? ''))
                : false;

            $didChangeTime = (!$isWalkIn && Schema::hasColumn('appointments', 'appointment_time'))
                ? ((string)Carbon::parse($appointment->appointment_time ?? '')->format('H:i') !== (string)($incomingTime ?? ''))
                : false;

            $requireNote = ($didChangeDoctor || $didChangeDate || $didChangeTime);

            $request->validate([
                'doctor_id' => $doctorRequired
                    ? ['nullable', 'integer', 'exists:doctors,id']
                    : ['nullable', 'integer', 'exists:doctors,id'],

                'appointment_date' => ['nullable', 'date', 'after_or_equal:today'],

                // walk-in: time not required; scheduled: accept H:i
                'appointment_time' => $isWalkIn ? ['nullable'] : ['nullable', 'date_format:H:i'],

                // ✅ reason/note if changed
                'staff_note' => $requireNote
                    ? ['required', 'string', 'max:500']
                    : ['nullable', 'string', 'max:500'],
            ]);

            $finalDate = $incomingDate;
            $finalDoctorId = $incomingDoctorId;
            $finalTime = $incomingTime;
            $finalNote = $request->filled('staff_note') ? trim((string)$request->staff_note) : null;

            if (empty($finalDate)) {
                throw new \RuntimeException('Please set an appointment date before approving.');
            }

            if ($doctorRequired && empty($finalDoctorId)) {
                throw new \RuntimeException('Please select a doctor before approving.');
            }

            if (!$isWalkIn && empty($finalTime)) {
                throw new \RuntimeException('Please select a time before approving.');
            }

            // ✅ Validate slot availability for scheduled services (exclude this appointment itself)
            if (!$isWalkIn && !empty($finalTime)) {
                $slots = $this->computeHourlySlots($finalDate, $finalDoctorId, $appointment->id);
                if (!in_array($finalTime, $slots, true)) {
                    throw new \RuntimeException('That time slot is no longer available. Please choose another.');
                }
            }

            DB::transaction(function () use ($appointment, $finalDate, $finalTime, $finalDoctorId, $finalNote, $isWalkIn) {
                $appointment->loadMissing('user');

                // ✅ Ensure patient record exists if patient_id column exists
                if (Schema::hasColumn('appointments', 'patient_id') && empty($appointment->patient_id)) {
                    $patientId = $this->findOrCreatePatientFromAppointment($appointment);
                    if (!$patientId) {
                        throw new \RuntimeException('Cannot approve: Patient record could not be created.');
                    }
                    $appointment->patient_id = $patientId;
                }

                // ✅ Apply edits BEFORE approving
                if (Schema::hasColumn('appointments', 'appointment_date')) {
                    $appointment->appointment_date = $finalDate;
                }

                if (Schema::hasColumn('appointments', 'doctor_id')) {
                    $appointment->doctor_id = $finalDoctorId;
                }

                if (Schema::hasColumn('appointments', 'dentist_name')) {
                    $appointment->dentist_name = $finalDoctorId
                        ? Doctor::whereKey($finalDoctorId)->value('name')
                        : ($appointment->dentist_name ?? null);
                }

                if (Schema::hasColumn('appointments', 'appointment_time')) {
                    $appointment->appointment_time = $isWalkIn ? null : $finalTime;
                }

                if (Schema::hasColumn('appointments', 'staff_note')) {
                    $appointment->staff_note = $finalNote;
                }

                // If scheduled and duration_minutes missing, enforce the 1-hour block
                if (!$isWalkIn && Schema::hasColumn('appointments', 'duration_minutes') && empty($appointment->duration_minutes)) {
                    $appointment->duration_minutes = self::SLOT_MINUTES;
                }

                // ✅ Approve
                if (Schema::hasColumn('appointments', 'status')) {
                    $appointment->status = 'upcoming';
                }

                $appointment->save();
            });

            $appointment->refresh()->loadMissing(['user', 'service', 'doctor']);

            // Notify only when status changes to upcoming
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

            return redirect()->route('admin.approvals.index')->with('success', 'Booking approved.');
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'      => false,
                    'message' => $e->getMessage() ?: 'Approval failed.',
                ], 422);
            }

            return redirect()->route('admin.approvals.index')->with('error', $e->getMessage() ?: 'Approval failed.');
        }
    }

    public function decline(Request $request, Appointment $appointment)
    {
        try {
            $previousStatus = Schema::hasColumn('appointments', 'status') ? ($appointment->status ?? null) : null;

            // optional reason for decline
            $request->validate([
                'staff_note' => ['nullable', 'string', 'max:500'],
            ]);

            if (Schema::hasColumn('appointments', 'staff_note') && $request->filled('staff_note')) {
                $appointment->staff_note = trim((string)$request->staff_note);
            }

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

            return redirect()->route('admin.approvals.index')->with('success', 'Booking declined.');
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'      => false,
                    'message' => $e->getMessage() ?: 'Decline failed.',
                ], 422);
            }

            return redirect()->route('admin.approvals.index')->with('error', 'Decline failed.');
        }
    }

    // -------------------------
    // SLOT + DOCTOR HELPERS
    // -------------------------

    private function isWalkIn(Service $service): bool
    {
        $durRaw = $service->duration_minutes ?? null;
        if ($durRaw === null || $durRaw === '') return true;

        if (is_numeric($durRaw)) {
            $d = (int) $durRaw;
            return $d > 0 && $d <= 5;
        }

        return false;
    }

    private function activeDoctors()
    {
        if (!class_exists(Doctor::class) || !Schema::hasTable('doctors')) return collect();

        $q = Doctor::query();

        if (Schema::hasColumn('doctors', 'is_active')) $q->where('is_active', 1);

        if (Schema::hasColumn('doctors', 'name')) $q->orderBy('name');
        else $q->orderBy('id');

        return $q->get();
    }

    private function doctorRequired(): bool
    {
        return Schema::hasTable('doctors')
            && Schema::hasColumn('appointments', 'doctor_id')
            && $this->activeDoctors()->count() > 0;
    }

    private function parseTimeOnDate(string $date, $time, string $tz): ?Carbon
    {
        if (empty($time)) return null;
        $timeStr = is_string($time) ? $time : (string) $time;

        try {
            return Carbon::parse(trim($date . ' ' . $timeStr), $tz)->seconds(0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function ceilToStep(Carbon $dt, int $stepMinutes): Carbon
    {
        $d = $dt->copy()->seconds(0);

        $minute = (int) $d->minute;
        $remainder = $minute % $stepMinutes;

        if ($remainder !== 0) {
            $d->addMinutes($stepMinutes - $remainder);
        }

        return $d;
    }

    /**
     * Compute available hourly slots for a date/doctor using the same rules as public booking,
     * but EXCLUDING a given appointment ID (so keeping the same slot doesn't block itself).
     */
    private function computeHourlySlots(string $date, ?int $doctorId, ?int $excludeAppointmentId = null): array
    {
        $tz = config('app.timezone');

        // Sunday closed
        $day = Carbon::parse($date, $tz)->dayOfWeekIso;
        if ($day === 7) return [];

        $openStart = Carbon::parse("$date " . self::CLINIC_OPEN, $tz);
        $openEnd   = Carbon::parse("$date " . self::CLINIC_CLOSE, $tz);

        if (!Schema::hasColumn('appointments', 'appointment_time')) return [];
        if (!Schema::hasColumn('appointments', 'appointment_date')) return [];

        $minStart = $openStart->copy();
        $now = now($tz);

        if ($openStart->isSameDay($now)) {
            $minCandidate = $now->copy()->addMinutes(self::LEAD_MINUTES_TODAY);
            $minStart = $this->ceilToStep($minCandidate, self::SLOT_MINUTES);
            if ($minStart->lt($openStart)) $minStart = $openStart->copy();
        }

        $bookedQuery = Appointment::query()
            ->select(['appointment_time'])
            ->whereDate('appointment_date', $date);

        if ($excludeAppointmentId) {
            $bookedQuery->where('id', '!=', $excludeAppointmentId);
        }

        if (Schema::hasColumn('appointments', 'doctor_id')) {
            $bookedQuery->addSelect('doctor_id');
        }

        if (Schema::hasColumn('appointments', 'duration_minutes')) {
            $bookedQuery->addSelect('duration_minutes');
        }

        if (Schema::hasColumn('appointments', 'status')) {
            $bookedQuery->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['cancelled', 'canceled', 'declined', 'rejected']);
            });
        }

        $blocked = $bookedQuery->get()->map(function ($row) use ($date, $tz) {
            $start = $this->parseTimeOnDate($date, $row->appointment_time, $tz);
            if (!$start) return null;

            $dur = self::SLOT_MINUTES;
            if (Schema::hasColumn('appointments', 'duration_minutes') && !empty($row->duration_minutes)) {
                $dur = max(1, (int) $row->duration_minutes);
            }

            $end = $start->copy()->addMinutes($dur);

            return [
                'start' => $start,
                'end' => $end,
                'doctor_id' => (Schema::hasColumn('appointments', 'doctor_id') ? ($row->doctor_id ?? null) : null),
            ];
        })->filter()->values();

        $slots = [];
        $cursor = $openStart->copy();

        while ($cursor->lt($openEnd)) {
            if ($cursor->lt($minStart)) {
                $cursor->addMinutes(self::SLOT_MINUTES);
                continue;
            }

            $candidateEnd = $cursor->copy()->addMinutes(self::SLOT_MINUTES);
            if ($candidateEnd->gt($openEnd)) break;

            $overlapAll = 0;
            $overlapDoctor = 0;

            foreach ($blocked as $b) {
                $bStart = $b['start'];
                $bEnd = $b['end'];

                $isOverlap = $cursor->lt($bEnd) && $candidateEnd->gt($bStart);
                if (!$isOverlap) continue;

                $overlapAll++;

                if ($doctorId && !empty($b['doctor_id']) && (int)$b['doctor_id'] === (int)$doctorId) {
                    $overlapDoctor++;
                }
            }

            if ($overlapAll < self::CHAIRS && ($doctorId ? $overlapDoctor === 0 : true)) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }

    // -------------------------
    // Existing Patient helpers
    // -------------------------

    private function findOrCreatePatientFromAppointment(Appointment $a): ?int
    {
        if (!Schema::hasTable('patients')) return null;

        $u = $a->user;

        $first   = $a->public_first_name ?? null;
        $middle  = $a->public_middle_name ?? null;
        $last    = $a->public_last_name ?? null;

        $email   = $a->public_email ?? null;
        $phone   = $a->public_phone ?? null;
        $address = $a->public_address ?? null;

        $gender    = $a->public_gender ?? ($a->gender ?? null);
        $birthdate = $a->public_birthdate ?? ($a->birthdate ?? null);

        if ($u) {
            if (empty($email) && !empty($u->email)) $email = $u->email;

            if (empty($phone) && Schema::hasColumn('users', 'phone_number') && !empty($u->phone_number)) {
                $phone = $u->phone_number;
            }

            if (empty($address) && Schema::hasColumn('users', 'address') && !empty($u->address)) {
                $address = $u->address;
            }

            if (empty($birthdate) && Schema::hasColumn('users', 'birthdate') && !empty($u->birthdate)) {
                $birthdate = $u->birthdate;
            }

            if (empty($first) && empty($last) && !empty($u->name)) {
                $parts = $this->splitName($u->name);
                $first  = $parts['first'] ?: $first;
                $middle = $parts['middle'] ?: $middle;
                $last   = $parts['last'] ?: $last;
            }
        }

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

        if ($u && Schema::hasColumn('patients', 'user_id') && empty($patient->user_id)) {
            $patient->user_id = $u->id;
        }

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

        if (Schema::hasColumn('patients', 'birthdate') && empty($patient->birthdate)) {
            $patient->birthdate = '2000-01-01';
        }

        $patient->save();

        return $patient->id;
    }

    private function splitName(string $name): array
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '') {
            return ['first' => null, 'middle' => null, 'last' => null];
        }

        $parts = explode(' ', $name);
        if (count($parts) === 1) {
            return ['first' => $parts[0], 'middle' => null, 'last' => null];
        }

        $first = array_shift($parts);
        $last = array_pop($parts);
        $middle = count($parts) ? implode(' ', $parts) : null;

        return ['first' => $first, 'middle' => $middle, 'last' => $last];
    }
}
