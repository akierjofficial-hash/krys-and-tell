<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicBookingController extends Controller
{
    public function create(Service $service)
    {
        $doctors = $this->activeDoctors();

        // ✅ Same-page success card support
        $successAppointment = null;
        if (session('booking_success') && session('success_appointment_id')) {
            $successAppointment = Appointment::with(['service', 'doctor'])
                ->whereKey(session('success_appointment_id'))
                ->first();
        }

        // ✅ Determine if we still need contact/address/birthdate from this user
        $user = auth()->user();
        $profile = $this->knownBookingDetails($user);
        $needsDetails = $user ? !$profile['complete'] : true;

        return view('public.booking.create', [
            'service' => $service,
            'doctors' => $doctors,
            'successAppointment' => $successAppointment,

            // ✅ for blade logic
            'needsDetails' => $needsDetails,
            'profile' => $profile,
        ]);
    }

    public function slots(Request $request, Service $service)
    {
        $doctorRequired = $this->doctorRequired();

        $request->validate([
            'date'      => ['required', 'date', 'after_or_equal:today'],
            'doctor_id' => $doctorRequired ? ['required', 'integer', 'exists:doctors,id'] : ['nullable', 'integer'],
        ]);

        $date = Carbon::parse($request->date)->toDateString();
        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        $durationMinutes = $this->serviceDurationMinutes($service);
        $slots = $this->computeSlots($date, $doctorId, $durationMinutes);

        return response()->json([
            'date'      => $date,
            'doctor_id' => $doctorId,
            'slots'     => $slots,
            'meta' => [
                'step_minutes' => 30,
                'duration_minutes' => $durationMinutes,
                'lead_minutes_today' => 60,
                'open' => '09:00',
                'close' => '18:00',
                'closed_weekday' => 'Sunday',
                'timezone' => config('app.timezone'),
            ],
        ]);
    }

    public function store(Request $request, Service $service)
    {
        $doctorRequired = $this->doctorRequired();
        $user = auth()->user();

        // ✅ Decide if we should require details this time
        $profile = $this->knownBookingDetails($user);
        $needsDetails = $user ? !$profile['complete'] : true;

        $rules = [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],

            'doctor_id' => $doctorRequired
                ? ['required', 'integer', 'exists:doctors,id']
                : ['nullable', 'integer', 'exists:doctors,id'],

            // ✅ only required on first booking (or when missing)
            'contact'   => $needsDetails ? ['required', 'string', 'max:40'] : ['nullable', 'string', 'max:40'],
            'address'   => $needsDetails ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
            'birthdate' => $needsDetails ? ['required', 'date', 'before:today', 'after:1900-01-01'] : ['nullable', 'date'],

            'message' => ['nullable', 'string', 'max:500'],
        ];

        // (Your booking is likely auth-only, but keep robust)
        if (!$user) {
            $rules = array_merge($rules, [
                'first_name'  => ['required', 'string', 'max:120'],
                'middle_name' => ['nullable', 'string', 'max:120'],
                'last_name'   => ['required', 'string', 'max:120'],
                'email'       => ['required', 'email', 'max:190'],
            ]);
        } else {
            $rules = array_merge($rules, [
                'first_name'  => ['nullable', 'string', 'max:120'],
                'middle_name' => ['nullable', 'string', 'max:120'],
                'last_name'   => ['nullable', 'string', 'max:120'],
                'email'       => ['nullable', 'email', 'max:190'],
            ]);
        }

        $request->validate($rules);

        $date = Carbon::parse($request->date)->toDateString();
        $time = $request->time;
        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        $durationMinutes = $this->serviceDurationMinutes($service);

        // ✅ TIME TRAP: re-check availability at submit time
        $available = in_array($time, $this->computeSlots($date, $doctorId, $durationMinutes), true);
        if (!$available) {
            return back()
                ->withErrors(['time' => 'That time slot is no longer available. Please choose another.'])
                ->withInput();
        }

        // ✅ Identity fields
        $first = $request->first_name;
        $middle = $request->middle_name;
        $last = $request->last_name;

        if ($user) {
            $nameParts = $this->splitName($user->name ?? '');
            $first  = $first  ?: $nameParts['first'];
            $middle = $middle ?: $nameParts['middle'];
            $last   = $last   ?: $nameParts['last'];
        }

        $fullName = trim(($first ?: '') . ' ' . ($middle ? trim($middle) . ' ' : '') . ($last ?: ''));
        if ($user && empty($fullName)) $fullName = $user->name ?? 'User';
        if (!$user && empty($fullName)) $fullName = 'Guest';

        $email = $user ? ($user->email ?? $request->email) : $request->email;

        // ✅ Use submitted details if present, otherwise fall back to stored details
        $contact = $request->filled('contact') ? $request->contact : ($profile['contact'] ?? null);
        $address = $request->filled('address') ? $request->address : ($profile['address'] ?? null);

        $birthdate = null;
        if ($request->filled('birthdate')) {
            $birthdate = Carbon::parse($request->birthdate)->toDateString();
        } elseif (!empty($profile['birthdate'])) {
            try { $birthdate = Carbon::parse($profile['birthdate'])->toDateString(); } catch (\Throwable $e) {}
        }

        return DB::transaction(function () use (
            $request,
            $service,
            $date,
            $time,
            $doctorId,
            $fullName,
            $durationMinutes,
            $user,
            $first,
            $middle,
            $last,
            $email,
            $contact,
            $address,
            $birthdate
        ) {
            // ✅ Optional: if users table has these cols, capture them once
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
            if (Schema::hasColumn('appointments', 'appointment_time')) {
                $appointment->appointment_time = $time;
            }

            if (Schema::hasColumn('appointments', 'duration_minutes')) {
                $appointment->duration_minutes = $durationMinutes;
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

            // ensure email is consistent with logged-in user
            if ($user && Schema::hasColumn('appointments', 'public_email')) {
                $appointment->public_email = $user->email;
            }

            $appointment->save();

            return redirect()
                ->route('public.booking.create', $service->id)
                ->with('booking_success', true)
                ->with('success_appointment_id', $appointment->id);
        });
    }

    /**
     * ✅ Detect existing contact/address/birthdate so we can hide fields on next bookings.
     */
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

        // 1) users table (optional)
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

        // 2) patients table (best)
        if (Schema::hasTable('patients')) {
            $pq = Patient::query();

            if (Schema::hasColumn('patients', 'user_id')) {
                $pq->where('user_id', $user->id);
            } elseif (Schema::hasColumn('patients', 'email') && !empty($user->email)) {
                $pq->where('email', $user->email);
            }

            $patient = $pq->first();
            if ($patient) {
                // contact
                if (empty($data['contact'])) {
                    foreach (['contact', 'contact_number', 'phone', 'mobile'] as $col) {
                        if (Schema::hasColumn('patients', $col) && !empty($patient->{$col})) {
                            $data['contact'] = $patient->{$col};
                            $data['source'] = 'patients.' . $col;
                            break;
                        }
                    }
                }

                // address
                if (empty($data['address']) && Schema::hasColumn('patients', 'address') && !empty($patient->address)) {
                    $data['address'] = $patient->address;
                    $data['source'] = $data['source'] ?? 'patients.address';
                }

                // birthdate
                if (empty($data['birthdate']) && Schema::hasColumn('patients', 'birthdate') && !empty($patient->birthdate)) {
                    $data['birthdate'] = $patient->birthdate;
                    $data['source'] = $data['source'] ?? 'patients.birthdate';
                }
            }
        }

        // 3) last appointment fallback
        if (Schema::hasTable('appointments')) {
            $aq = Appointment::query()
                ->latest()
                ->when(Schema::hasColumn('appointments', 'user_id'), fn($q) => $q->where('user_id', $user->id))
                ->when(!Schema::hasColumn('appointments', 'user_id') && Schema::hasColumn('appointments', 'public_email'),
                    fn($q) => $q->where('public_email', $user->email)
                );

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

        $data['complete'] = !empty($data['contact']) && !empty($data['address']) && !empty($data['birthdate']);
        return $data;
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

    private function computeSlots(string $date, ?int $doctorId, int $durationMinutes): array
    {
        $tz = config('app.timezone');

        // Sunday closed
        $day = Carbon::parse($date, $tz)->dayOfWeekIso; // 7 = Sunday
        if ($day === 7) return [];

        $openStart = Carbon::parse("$date 09:00", $tz);
        $openEnd   = Carbon::parse("$date 18:00", $tz);

        $stepMinutes = 30;
        $leadMinutesToday = 60;

        if (!Schema::hasColumn('appointments', 'appointment_time')) return [];
        if (!Schema::hasColumn('appointments', 'appointment_date')) return [];

        $minStart = $openStart->copy();
        $now = now($tz);

        if ($openStart->isSameDay($now)) {
            $minCandidate = $now->copy()->addMinutes($leadMinutesToday);
            $minStart = $this->ceilToStep($minCandidate, $stepMinutes);
            if ($minStart->lt($openStart)) $minStart = $openStart->copy();
        }

        $bookedQuery = Appointment::query()->select(['appointment_time']);
        if (Schema::hasColumn('appointments', 'duration_minutes')) {
            $bookedQuery->addSelect('duration_minutes');
        }

        $bookedQuery->whereDate('appointment_date', $date);

        if (Schema::hasColumn('appointments', 'status')) {
            $bookedQuery->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['cancelled', 'canceled', 'declined', 'rejected']);
            });
        }

        if ($doctorId && Schema::hasColumn('appointments', 'doctor_id')) {
            $bookedQuery->where('doctor_id', $doctorId);
        }

        $blocked = $bookedQuery->get()->map(function ($row) use ($date, $tz) {
            $start = $this->parseTimeOnDate($date, $row->appointment_time, $tz);
            if (!$start) return null;

            $dur = 60;
            if (Schema::hasColumn('appointments', 'duration_minutes') && !empty($row->duration_minutes)) {
                $dur = max(1, (int) $row->duration_minutes);
            }

            $end = $start->copy()->addMinutes($dur);
            return [$start, $end];
        })->filter()->values();

        $slots = [];
        $cursor = $openStart->copy();

        while ($cursor->lt($openEnd)) {
            if ($cursor->lt($minStart)) {
                $cursor->addMinutes($stepMinutes);
                continue;
            }

            $candidateEnd = $cursor->copy()->addMinutes($durationMinutes);
            if ($candidateEnd->gt($openEnd)) break;

            $overlap = false;
            foreach ($blocked as [$bStart, $bEnd]) {
                if ($cursor->lt($bEnd) && $candidateEnd->gt($bStart)) {
                    $overlap = true;
                    break;
                }
            }

            if (!$overlap) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes($stepMinutes);
        }

        return $slots;
    }

    private function serviceDurationMinutes(Service $service): int
    {
        $d = 60;
        if (Schema::hasColumn('services', 'duration_minutes') && !empty($service->duration_minutes)) {
            $d = (int) $service->duration_minutes;
        }
        return max(15, min(240, $d));
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
