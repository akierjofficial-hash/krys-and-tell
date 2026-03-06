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
    private const PENDING_STATUS = 'pending';
    private const SLOT_BLOCKING_STATUSES = ['upcoming', 'approved', 'confirmed', 'scheduled'];

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
        $user = auth()->user();

        $successAppointment = null;
        if (session('booking_success') && session('success_appointment_id')) {
            $candidate = Appointment::with(['service', 'doctor'])
                ->whereKey(session('success_appointment_id'))
                ->first();

            if ($candidate && $this->userOwnsBooking($candidate, $user)) {
                $successAppointment = $candidate;
            }
        }

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

    public function edit(Appointment $appointment)
    {
        $user = auth()->user();
        if (!$this->isPendingBookingEditableByUser($appointment, $user)) {
            abort(403);
        }

        $appointment->loadMissing(['service', 'doctor']);

        $service = $appointment->service;
        if (
            !$service
            && Schema::hasColumn('appointments', 'service_id')
            && !empty($appointment->service_id)
        ) {
            $service = Service::find($appointment->service_id);
        }

        if (!$service) {
            return redirect()->route('profile.show')
                ->with('error', 'Unable to edit booking because service data is missing.');
        }

        $dateValue = null;
        $timeValue = null;

        try {
            if (!empty($appointment->appointment_date)) {
                $dateValue = Carbon::parse($appointment->appointment_date)->toDateString();
            }
            if (!empty($appointment->appointment_time)) {
                $timeValue = Carbon::parse($appointment->appointment_time)->format('H:i');
            }
        } catch (\Throwable $e) {
            // no-op
        }

        $doctorValue = null;
        if (Schema::hasColumn('appointments', 'doctor_id')) {
            $doctorValue = $appointment->doctor_id ?: null;
        }

        return view('public.booking.edit', [
            'appointment' => $appointment,
            'service' => $service,
            'doctors' => $this->activeDoctors(),
            'doctorRequired' => $this->doctorRequired(),
            'isWalkIn' => $this->isWalkIn($service),
            'prefillDate' => $dateValue,
            'prefillTime' => $timeValue,
            'prefillDoctorId' => $doctorValue,
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $user = auth()->user();
        if (!$this->isPendingBookingEditableByUser($appointment, $user)) {
            abort(403);
        }

        $appointment->loadMissing(['service', 'doctor']);

        $service = $appointment->service;
        if (
            !$service
            && Schema::hasColumn('appointments', 'service_id')
            && !empty($appointment->service_id)
        ) {
            $service = Service::find($appointment->service_id);
        }

        if (!$service) {
            return redirect()->route('profile.show')
                ->with('error', 'Unable to edit booking because service data is missing.');
        }

        $doctorRequired = $this->doctorRequired();
        $isWalkIn = $this->isWalkIn($service);

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => $isWalkIn ? ['nullable'] : ['required', 'date_format:H:i'],
            'doctor_id' => $doctorRequired
                ? ['required', 'integer', 'exists:doctors,id']
                : ['nullable', 'integer', 'exists:doctors,id'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $date = Carbon::parse($request->date)->toDateString();
        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);
        $time = $isWalkIn ? null : $request->time;

        if (!$isWalkIn) {
            $available = in_array($time, $this->computeHourlySlots($date, $doctorId, $appointment->id), true);
            if (!$available) {
                return back()
                    ->withErrors(['time' => 'That time slot is no longer available. Please choose another.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($appointment, $date, $time, $doctorId, $isWalkIn, $request) {
            if (Schema::hasColumn('appointments', 'appointment_date')) {
                $appointment->appointment_date = $date;
            }

            if (Schema::hasColumn('appointments', 'appointment_time')) {
                $appointment->appointment_time = $isWalkIn ? null : $time;
            }

            if (Schema::hasColumn('appointments', 'duration_minutes')) {
                $appointment->duration_minutes = self::SLOT_MINUTES;
            }

            if (Schema::hasColumn('appointments', 'doctor_id')) {
                $appointment->doctor_id = $doctorId;
            }

            if (Schema::hasColumn('appointments', 'dentist_name')) {
                $appointment->dentist_name = $doctorId
                    ? Doctor::whereKey($doctorId)->value('name')
                    : null;
            }

            if (Schema::hasColumn('appointments', 'public_message')) {
                $appointment->public_message = $request->input('message');
            }

            if (Schema::hasColumn('appointments', 'status')) {
                $appointment->status = self::PENDING_STATUS;
            }

            $appointment->save();
        });

        return redirect()
            ->route('public.booking.edit', $appointment->id)
            ->with('success', 'Your booking request has been updated.');
    }

    public function slots(Request $request, Service $service)
    {
        // ✅ Walk-in services do not have slots
        if ($this->isWalkIn($service)) {
            $doctorId = $request->integer('doctor_id') ?: null;
            $schedule = $this->resolveDoctorSchedule($doctorId);
            $walkInDate = now()->toDateString();
            if ($request->filled('date')) {
                try {
                    $walkInDate = Carbon::parse((string) $request->date)->toDateString();
                } catch (\Throwable $e) {
                    // Keep today fallback.
                }
            }

            return response()->json([
                'date' => $walkInDate,
                'doctor_id' => $doctorId,
                'slots' => [],
                'meta' => [
                    'walk_in' => true,
                    'open' => $schedule['open'],
                    'close' => $schedule['close'],
                    'working_days' => $schedule['working_days'],
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
        $schedule = $this->resolveDoctorSchedule($doctorId);

        $slots = $this->computeHourlySlots($date, $doctorId);

        return response()->json([
            'date'      => $date,
            'doctor_id' => $doctorId,
            'slots'     => $slots,
            'meta' => [
                'step_minutes' => self::SLOT_MINUTES,
                'duration_minutes' => self::SLOT_MINUTES,
                'lead_minutes_today' => self::LEAD_MINUTES_TODAY,
                'open' => $schedule['open'],
                'close' => $schedule['close'],
                'chairs' => self::CHAIRS,
                'working_days' => $schedule['working_days'],
                'timezone' => config('app.timezone'),
            ],
        ]);
    }

    public function store(Request $request, Service $service)
    {
        $doctorRequired = $this->doctorRequired();
        $user = auth()->user();

        $isWalkInService = $this->isWalkIn($service);

        $profile = $this->knownBookingDetails($user);
        $needsDetails = $user ? !$profile['complete'] : true;

        $rules = [
            'date' => ['required', 'date', 'after_or_equal:today'],

            // Scheduled services can submit either:
            // - normal slot booking (time required)
            // - fallback walk-in request (request_walkin=1, no time)
            'time' => $isWalkInService ? ['nullable'] : ['nullable', 'date_format:H:i'],
            'request_walkin' => ['nullable', 'boolean'],

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
        $requestWalkIn = !$isWalkInService && $request->boolean('request_walkin');
        $isWalkIn = $isWalkInService || $requestWalkIn;

        // Walk-in: store NULL time (no slot system).
        $time = $isWalkIn ? null : $request->time;

        if (!$isWalkInService && !$requestWalkIn && empty($time)) {
            return back()
                ->withErrors(['time' => 'Please select an available time slot.'])
                ->withInput();
        }

        // Fallback walk-in request is only for current-day fully booked schedules.
        if ($requestWalkIn) {
            $today = now()->toDateString();
            if ($date !== $today) {
                return back()
                    ->withErrors(['request_walkin' => 'Walk-in request is only available for today when fully booked.'])
                    ->withInput();
            }

            $slots = $this->computeHourlySlots($date, $doctorId);
            if (!empty($slots)) {
                return back()
                    ->withErrors(['request_walkin' => 'Available slots were found. Please choose a time instead of requesting walk-in.'])
                    ->withInput();
            }
        }

        // Only enforce slot availability for regular scheduled submissions.
        if (!$isWalkInService && !$requestWalkIn) {
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
            try {
                $birthdate = Carbon::parse($profile['birthdate'])->toDateString();
            } catch (\Throwable $e) {
                // no-op
            }
        }

        $existingPendingId = $this->findLatestPendingBookingIdForUserAndService($user, $service->id);

        // Keep only one pending request per user/service by updating the latest pending row.
        $tx = DB::transaction(function () use (
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
            $isWalkIn,
            $requestWalkIn,
            $existingPendingId
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

                if ($dirty) {
                    $user->save();
                }
            }

            $appointment = null;
            if (!empty($existingPendingId)) {
                $appointment = Appointment::query()
                    ->lockForUpdate()
                    ->find($existingPendingId);
            }

            $updatingExistingPending = $appointment !== null;

            if (!$appointment) {
                $appointment = new Appointment();
            }

            if (Schema::hasColumn('appointments', 'service_id')) {
                $appointment->service_id = $service->id;
            }
            if (Schema::hasColumn('appointments', 'appointment_date')) {
                $appointment->appointment_date = $date;
            }

            if (Schema::hasColumn('appointments', 'appointment_time')) {
                $appointment->appointment_time = $isWalkIn ? null : $time;
            }

            if (Schema::hasColumn('appointments', 'duration_minutes')) {
                $appointment->duration_minutes = self::SLOT_MINUTES;
            }

            if (Schema::hasColumn('appointments', 'is_walk_in_request')) {
                $appointment->is_walk_in_request = $requestWalkIn;
            }

            if (Schema::hasColumn('appointments', 'doctor_id')) {
                $appointment->doctor_id = $doctorId;
            }
            if (Schema::hasColumn('appointments', 'dentist_name')) {
                $appointment->dentist_name = $doctorId
                    ? Doctor::whereKey($doctorId)->value('name')
                    : null;
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
                $appointment->status = self::PENDING_STATUS;
            }

            if ($user && Schema::hasColumn('appointments', 'user_id')) {
                $appointment->user_id = $user->id;
            }

            // If logged-in, always trust user email.
            if ($user && Schema::hasColumn('appointments', 'public_email')) {
                $appointment->public_email = $user->email;
            }

            $appointment->save();

            return [
                'appointment' => $appointment,
                'updated_existing_pending' => $updatingExistingPending,
            ];
        });

        /** @var \App\Models\Appointment $appointment */
        $appointment = $tx['appointment'];
        $updatedExistingPending = (bool) ($tx['updated_existing_pending'] ?? false);

        // Notify only for new pending rows, not for in-place user edits.
        if (!$updatedExistingPending) {
            $this->notifyClinicNewBooking($appointment);

            try {
                $appointment->loadMissing(['service']);
                app(\App\Services\WebPushService::class)->notifyNewBooking($appointment);
            } catch (\Throwable $e) {
                // silent
            }
        }

        return redirect()
            ->route('public.booking.create', $service->id)
            ->with('booking_success', true)
            ->with('booking_updated', $updatedExistingPending)
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

    private function computeHourlySlots(string $date, ?int $doctorId, ?int $excludeAppointmentId = null): array
    {
        $tz = config('app.timezone');
        $schedule = $this->resolveDoctorSchedule($doctorId);
        if (!$this->doctorWorksOnDate($schedule, $date, $tz)) return [];

        $openStart = Carbon::parse("$date " . $schedule['open'], $tz);
        $openEnd   = Carbon::parse("$date " . $schedule['close'], $tz);

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

        if (!empty($excludeAppointmentId)) {
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
                    ->orWhereIn('status', self::SLOT_BLOCKING_STATUSES);
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

    private function findLatestPendingBookingIdForUserAndService($user, int $serviceId): ?int
    {
        $q = $this->ownedBookingsQuery($user);
        if (!$q || !Schema::hasColumn('appointments', 'status')) {
            return null;
        }

        $q->where('status', self::PENDING_STATUS);

        if (Schema::hasColumn('appointments', 'service_id')) {
            $q->where('service_id', $serviceId);
        }

        if (Schema::hasColumn('appointments', 'appointment_date')) {
            $q->whereDate('appointment_date', '>=', now()->toDateString());
        }

        return $q->latest('id')->value('id');
    }

    private function isPendingBookingEditableByUser(Appointment $appointment, $user): bool
    {
        if (!$user) return false;
        if (!$this->userOwnsBooking($appointment, $user)) return false;

        if (Schema::hasColumn('appointments', 'status')) {
            return strtolower((string) ($appointment->status ?? '')) === self::PENDING_STATUS;
        }

        return false;
    }

    private function userOwnsBooking(Appointment $appointment, $user): bool
    {
        if (!$user) return false;

        if (Schema::hasColumn('appointments', 'user_id') && !empty($appointment->user_id)) {
            if ((int) $appointment->user_id === (int) $user->id) {
                return true;
            }
        }

        if (
            Schema::hasColumn('appointments', 'public_email')
            && !empty($appointment->public_email)
            && !empty($user->email)
        ) {
            return strcasecmp((string) $appointment->public_email, (string) $user->email) === 0;
        }

        return false;
    }

    private function ownedBookingsQuery($user): ?\Illuminate\Database\Eloquent\Builder
    {
        if (!$user || !Schema::hasTable('appointments')) return null;

        $q = Appointment::query();

        if (Schema::hasColumn('appointments', 'user_id')) {
            return $q->where('user_id', $user->id);
        }

        if (Schema::hasColumn('appointments', 'public_email') && !empty($user->email)) {
            return $q->where('public_email', $user->email);
        }

        return null;
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

    private function defaultWorkingDays(): array
    {
        return [1, 2, 3, 4, 5, 6]; // Mon-Sat
    }

    private function normalizeWorkingDays($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (!is_array($raw)) {
            return $this->defaultWorkingDays();
        }

        $days = collect($raw)
            ->map(fn ($d) => (int) $d)
            ->filter(fn ($d) => $d >= 1 && $d <= 7)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return empty($days) ? $this->defaultWorkingDays() : $days;
    }

    private function resolveDoctorSchedule(?int $doctorId): array
    {
        $open = self::CLINIC_OPEN;
        $close = self::CLINIC_CLOSE;
        $workingDays = $this->defaultWorkingDays();

        if ($doctorId && class_exists(Doctor::class) && Schema::hasTable('doctors')) {
            $doctor = Doctor::query()->whereKey($doctorId)->first();

            if ($doctor) {
                if (Schema::hasColumn('doctors', 'working_days')) {
                    $workingDays = $this->normalizeWorkingDays($doctor->working_days ?? null);
                }

                if (Schema::hasColumn('doctors', 'work_start_time') && !empty($doctor->work_start_time)) {
                    try {
                        $open = Carbon::parse($doctor->work_start_time)->format('H:i');
                    } catch (\Throwable $e) {
                        $open = self::CLINIC_OPEN;
                    }
                }

                if (Schema::hasColumn('doctors', 'work_end_time') && !empty($doctor->work_end_time)) {
                    try {
                        $close = Carbon::parse($doctor->work_end_time)->format('H:i');
                    } catch (\Throwable $e) {
                        $close = self::CLINIC_CLOSE;
                    }
                }
            }
        }

        // Guard against invalid/zero-length schedules.
        try {
            $start = Carbon::createFromFormat('H:i', $open);
            $end = Carbon::createFromFormat('H:i', $close);
            if (!$end->gt($start)) {
                $open = self::CLINIC_OPEN;
                $close = self::CLINIC_CLOSE;
            }
        } catch (\Throwable $e) {
            $open = self::CLINIC_OPEN;
            $close = self::CLINIC_CLOSE;
        }

        return [
            'open' => $open,
            'close' => $close,
            'working_days' => $workingDays,
        ];
    }

    private function doctorWorksOnDate(array $schedule, string $date, string $tz): bool
    {
        $dayIso = Carbon::parse($date, $tz)->dayOfWeekIso;
        $workingDays = $this->normalizeWorkingDays($schedule['working_days'] ?? null);
        return in_array($dayIso, $workingDays, true);
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

