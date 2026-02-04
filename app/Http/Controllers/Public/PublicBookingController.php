<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Mail\NewBookingNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PublicBookingController extends Controller
{
    // ✅ Clinic rules (scheduled bookings)
    private const CLINIC_OPEN  = '09:00';
    private const CLINIC_CLOSE = '17:00'; // last start slot is 16:00
    private const SLOT_MINUTES = 60;      // 1 hour blocks
    private const CHAIRS       = 2;       // 2 chairs = 2 patients per hour
    private const LEAD_MINUTES_TODAY = 60;

    // ✅ Walk-in rule:
    // - duration_minutes is null/empty => walk-in
    // - duration_minutes 1–5 => walk-in
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

    public function create(Service $service)
    {
        $doctors = $this->activeDoctors();

        $successAppointment = null;
        if (session('booking_success') && session('success_appointment_id')) {
            $successAppointment = Appointment::with(['service', 'doctor'])
                ->whereKey(session('success_appointment_id'))
                ->first();
        }

        $user = auth()->user();
        $profile = $this->knownBookingDetails($user);
        $needsDetails = $user ? !$profile['complete'] : true;

        return view('public.booking.create', [
            'service' => $service,
            'doctors' => $doctors,
            'successAppointment' => $successAppointment,
            'needsDetails' => $needsDetails,
            'profile' => $profile,
            'isWalkIn' => $this->isWalkIn($service),
        ]);
    }

    public function slots(Request $request, Service $service)
    {
        // ✅ Walk-in services do not have slots
        if ($this->isWalkIn($service)) {
            return response()->json([
                'date' => Carbon::parse($request->date ?? now())->toDateString(),
                'doctor_id' => $request->integer('doctor_id') ?: null,
                'slots' => [],
                'meta' => [
                    'walk_in' => true,
                    'open' => self::CLINIC_OPEN,
                    'close' => self::CLINIC_CLOSE,
                    'timezone' => config('app.timezone'),
                ],
            ]);
        }

        $doctorRequired = $this->doctorRequired();

        $request->validate([
            'date'      => ['required', 'date', 'after_or_equal:today'],
            'doctor_id' => $doctorRequired ? ['required', 'integer', 'exists:doctors,id'] : ['nullable', 'integer'],
        ]);

        $date = Carbon::parse($request->date)->toDateString();
        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        $slots = $this->computeHourlySlots($date, $doctorId);

        return response()->json([
            'date'      => $date,
            'doctor_id' => $doctorId,
            'slots'     => $slots,
            'meta' => [
                'step_minutes' => self::SLOT_MINUTES,
                'duration_minutes' => self::SLOT_MINUTES,
                'lead_minutes_today' => self::LEAD_MINUTES_TODAY,
                'open' => self::CLINIC_OPEN,
                'close' => self::CLINIC_CLOSE,
                'chairs' => self::CHAIRS,
                'closed_weekday' => 'Sunday',
                'timezone' => config('app.timezone'),
            ],
        ]);
    }

    public function store(Request $request, Service $service)
    {
        $doctorRequired = $this->doctorRequired();
        $user = auth()->user();

        $isWalkIn = $this->isWalkIn($service);

        $profile = $this->knownBookingDetails($user);
        $needsDetails = $user ? !$profile['complete'] : true;

        $rules = [
            'date' => ['required', 'date', 'after_or_equal:today'],

            // ✅ Walk-in: time is NOT required
            'time' => $isWalkIn ? ['nullable'] : ['required', 'date_format:H:i'],

            'doctor_id' => $doctorRequired
                ? ['required', 'integer', 'exists:doctors,id']
                : ['nullable', 'integer', 'exists:doctors,id'],

            'full_name' => ['required', 'string', 'max:190'],

            'contact'   => $needsDetails ? ['required', 'string', 'max:40'] : ['nullable', 'string', 'max:40'],
            'address'   => $needsDetails ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
            'birthdate' => $needsDetails ? ['required', 'date', 'before:today', 'after:1900-01-01'] : ['nullable', 'date'],

            'message' => ['nullable', 'string', 'max:500'],
        ];

        $rules = array_merge($rules, [
            'email' => $user ? ['nullable', 'email', 'max:190'] : ['required', 'email', 'max:190'],
        ]);

        $request->validate($rules);

        $date = Carbon::parse($request->date)->toDateString();
        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        // ✅ Walk-in: store NULL time (no slot system)
        $time = $isWalkIn ? null : $request->time;

        // ✅ Only enforce chair-capacity rules for scheduled services
        if (!$isWalkIn) {
            $available = in_array($time, $this->computeHourlySlots($date, $doctorId), true);
            if (!$available) {
                return back()
                    ->withErrors(['time' => 'That time slot is no longer available. Please choose another.'])
                    ->withInput();
            }
        }

        $fullName = trim((string) $request->full_name);
        $nameParts = $this->splitName($fullName);
        $first  = $nameParts['first'];
        $middle = $nameParts['middle'];
        $last   = $nameParts['last'];

        $email = $user ? ($user->email ?? $request->email) : $request->email;

        $contact = $request->filled('contact') ? $request->contact : ($profile['contact'] ?? null);
        $address = $request->filled('address') ? $request->address : ($profile['address'] ?? null);

        $birthdate = null;
        if ($request->filled('birthdate')) {
            $birthdate = Carbon::parse($request->birthdate)->toDateString();
        } elseif (!empty($profile['birthdate'])) {
            try { $birthdate = Carbon::parse($profile['birthdate'])->toDateString(); } catch (\Throwable $e) {}
        }

        // ✅ Save first (transaction), then email AFTER commit
        $appointment = DB::transaction(function () use (
            $request,
            $service,
            $date,
            $time,
            $doctorId,
            $fullName,
            $user,
            $first,
            $middle,
            $last,
            $email,
            $contact,
            $address,
            $birthdate,
            $isWalkIn
        ) {
            if ($user) {
                $dirty = false;

                if (Schema::hasColumn('users', 'phone_number') && empty($user->phone_number) && !empty($contact)) {
                    $user->phone_number = $contact;
                    $dirty = true;
                }
                if (Schema::hasColumn('users', 'address') && empty($user->address) && !empty($address)) {
                    $user->address = $address;
                    $dirty = true;
                }
                if (Schema::hasColumn('users', 'birthdate') && empty($user->birthdate) && !empty($birthdate)) {
                    $user->birthdate = $birthdate;
                    $dirty = true;
                }

                if ($dirty) $user->save();
            }

            $appointment = new Appointment();

            if (Schema::hasColumn('appointments', 'service_id')) {
                $appointment->service_id = $service->id;
            }
            if (Schema::hasColumn('appointments', 'appointment_date')) {
                $appointment->appointment_date = $date;
            }

            // ✅ only set time if scheduled
            if (!$isWalkIn && Schema::hasColumn('appointments', 'appointment_time')) {
                $appointment->appointment_time = $time; // "H:i" => MySQL time becomes "H:i:s"
            }

            // ✅ Scheduled bookings block 1 hour; Walk-ins: do NOT block chairs
            if (!$isWalkIn && Schema::hasColumn('appointments', 'duration_minutes')) {
                $appointment->duration_minutes = self::SLOT_MINUTES;
            }

            if ($doctorId && Schema::hasColumn('appointments', 'doctor_id')) {
                $appointment->doctor_id = $doctorId;
            }
            if ($doctorId && Schema::hasColumn('appointments', 'dentist_name')) {
                $appointment->dentist_name = Doctor::whereKey($doctorId)->value('name');
            }

            if (Schema::hasColumn('appointments', 'public_name')) {
                $appointment->public_name = $fullName;
            }
            if (Schema::hasColumn('appointments', 'public_first_name')) {
                $appointment->public_first_name = $first;
            }
            if (Schema::hasColumn('appointments', 'public_middle_name')) {
                $appointment->public_middle_name = $middle;
            }
            if (Schema::hasColumn('appointments', 'public_last_name')) {
                $appointment->public_last_name = $last;
            }

            if (Schema::hasColumn('appointments', 'public_email')) {
                $appointment->public_email = $email;
            }
            if (Schema::hasColumn('appointments', 'public_phone') && !empty($contact)) {
                $appointment->public_phone = $contact;
            }
            if (Schema::hasColumn('appointments', 'public_address') && !empty($address)) {
                $appointment->public_address = $address;
            }
            if (Schema::hasColumn('appointments', 'public_birthdate') && !empty($birthdate)) {
                $appointment->public_birthdate = $birthdate;
            }

            if (Schema::hasColumn('appointments', 'public_message')) {
                $appointment->public_message = $request->message;
            }

            if (Schema::hasColumn('appointments', 'status')) {
                $appointment->status = 'pending';
            }

            if ($user && Schema::hasColumn('appointments', 'user_id')) {
                $appointment->user_id = $user->id;
            }

            // If logged-in, always trust user email
            if ($user && Schema::hasColumn('appointments', 'public_email')) {
                $appointment->public_email = $user->email;
            }

            $appointment->save();

            return $appointment;
        });

        // ✅ Send clinic notification email (won't block booking if email fails)
        $this->notifyClinicNewBooking($appointment);

        // ✅ Send Web Push notification to all staff/admin (if enabled)
        try {
            $appointment->loadMissing(['service']);
            app(\App\Services\WebPushService::class)->sendNewBooking($appointment);
        } catch (\Throwable $e) {
            // silent
        }

        return redirect()
            ->route('public.booking.create', $service->id)
            ->with('booking_success', true)
            ->with('success_appointment_id', $appointment->id);
    }

    /**
     * ✅ Notify clinic email that a new booking was submitted.
     * Uses MAIL_FROM_ADDRESS as default receiver, fallback to krysandt@gmail.com.
     */
    private function notifyClinicNewBooking(Appointment $appointment): void
{
    try {
        // Ensure relations exist for the email template
        $appointment->loadMissing(['service', 'doctor']);

        // Receiver (clinic inbox)
        // Optional: set MAIL_BOOKINGS_TO in .env, fallback to MAIL_FROM_ADDRESS
        $to = env('MAIL_BOOKINGS_TO', config('mail.from.address') ?: 'krysandt@gmail.com');

        Mail::to($to)->send(new NewBookingNotification($appointment));
    } catch (\Throwable $e) {
        Log::warning('Booking email notification failed: ' . $e->getMessage(), [
            'appointment_id' => $appointment->id ?? null,
        ]);
    }
}

    private function computeHourlySlots(string $date, ?int $doctorId): array
    {
        $tz = config('app.timezone');

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

    private function knownBookingDetails($user): array
    {
        $data = [
            'contact' => null,
            'address' => null,
            'birthdate' => null,
            'source' => null,
            'complete' => false,
        ];

        if (!$user) return $data;

        if (Schema::hasColumn('users', 'phone_number') && !empty($user->phone_number)) {
            $data['contact'] = $data['contact'] ?? $user->phone_number;
            $data['source'] = $data['source'] ?? 'users.phone_number';
        }
        if (Schema::hasColumn('users', 'address') && !empty($user->address)) {
            $data['address'] = $data['address'] ?? $user->address;
            $data['source'] = $data['source'] ?? 'users.address';
        }
        if (Schema::hasColumn('users', 'birthdate') && !empty($user->birthdate)) {
            $data['birthdate'] = $data['birthdate'] ?? $user->birthdate;
            $data['source'] = $data['source'] ?? 'users.birthdate';
        }

        if (Schema::hasTable('patients')) {
            $pq = Patient::query();

            if (Schema::hasColumn('patients', 'user_id')) {
                $pq->where('user_id', $user->id);
            } elseif (Schema::hasColumn('patients', 'email') && !empty($user->email)) {
                $pq->where('email', $user->email);
            }

            $patient = $pq->first();
            if ($patient) {
                if (empty($data['contact'])) {
                    foreach (['contact', 'contact_number', 'phone', 'mobile'] as $col) {
                        if (Schema::hasColumn('patients', $col) && !empty($patient->{$col})) {
                            $data['contact'] = $patient->{$col};
                            $data['source'] = 'patients.' . $col;
                            break;
                        }
                    }
                }

                if (empty($data['address']) && Schema::hasColumn('patients', 'address') && !empty($patient->address)) {
                    $data['address'] = $patient->address;
                    $data['source'] = $data['source'] ?? 'patients.address';
                }

                if (empty($data['birthdate']) && Schema::hasColumn('patients', 'birthdate') && !empty($patient->birthdate)) {
                    $data['birthdate'] = $patient->birthdate;
                    $data['source'] = $data['source'] ?? 'patients.birthdate';
                }
            }
        }

        if (Schema::hasTable('appointments')) {
            $aq = Appointment::query()->latest();

            if (Schema::hasColumn('appointments', 'user_id')) {
                $aq->where('user_id', $user->id);
            } elseif (Schema::hasColumn('appointments', 'public_email') && !empty($user->email)) {
                $aq->where('public_email', $user->email);
            } else {
                $aq = null;
            }

            if ($aq) {
                $last = $aq->first();
                if ($last) {
                    if (empty($data['contact']) && !empty($last->public_phone)) {
                        $data['contact'] = $last->public_phone;
                        $data['source'] = $data['source'] ?? 'appointments.public_phone';
                    }
                    if (empty($data['address']) && !empty($last->public_address)) {
                        $data['address'] = $last->public_address;
                        $data['source'] = $data['source'] ?? 'appointments.public_address';
                    }
                    if (empty($data['birthdate']) && !empty($last->public_birthdate)) {
                        $data['birthdate'] = $last->public_birthdate;
                        $data['source'] = $data['source'] ?? 'appointments.public_birthdate';
                    }
                }
            }
        }

        $data['complete'] = !empty($data['contact']) && !empty($data['address']) && !empty($data['birthdate']);
        return $data;
    }

    private function splitName(string $name): array
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '') return ['first' => null, 'middle' => null, 'last' => null];

        $parts = explode(' ', $name);

        if (count($parts) === 1) {
            return ['first' => $parts[0], 'middle' => null, 'last' => $parts[0]];
        }

        $first = array_shift($parts);
        $last = array_pop($parts);
        $middle = count($parts) ? implode(' ', $parts) : null;

        return ['first' => $first, 'middle' => $middle, 'last' => $last];
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
}