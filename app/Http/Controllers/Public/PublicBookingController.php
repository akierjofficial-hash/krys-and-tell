<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
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

        // ✅ Optional: pass user for autofill in blade
        $authUser = auth()->user();

        return view('public.booking.create', [
            'service' => $service,
            'doctors' => $doctors,
            'successAppointment' => $successAppointment,
            'authUser' => $authUser,
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

        // ✅ If logged-in and already has phone, contact can be optional.
        $needsContact = true;
        if ($user && Schema::hasColumn('users', 'phone_number')) {
            $needsContact = empty($user->phone_number);
        }

        // ✅ Validation rules adapt based on login
        $rules = [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],

            'doctor_id' => $doctorRequired
                ? ['required', 'integer', 'exists:doctors,id']
                : ['nullable', 'integer', 'exists:doctors,id'],

            // Contact + address (user will fill contact, address optional)
            'contact' => $needsContact ? ['required', 'string', 'max:40'] : ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],

            'message' => ['nullable', 'string', 'max:500'],
        ];

        // ✅ Only require name/email if NOT logged in
        if (!$user) {
            $rules = array_merge($rules, [
                'first_name'  => ['required', 'string', 'max:120'],
                'middle_name' => ['nullable', 'string', 'max:120'],
                'last_name'   => ['required', 'string', 'max:120'],
                'email'       => ['required', 'email', 'max:190'],
            ]);
        } else {
            // still accept them if your form sends them
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

        // ✅ Build identity fields:
        // - If logged in: use user.name + user.email
        // - Else: use request fields
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
        if ($user && empty($fullName)) {
            $fullName = $user->name ?? 'User';
        }
        if (!$user && empty($fullName)) {
            $fullName = 'Guest';
        }

        $email = $user ? ($user->email ?? $request->email) : $request->email;

        // ✅ contact/address:
        // - If user has phone/address saved, use it unless user enters new data
        $contact = $request->contact;
        if ($user && Schema::hasColumn('users', 'phone_number') && empty($contact)) {
            $contact = $user->phone_number;
        }

        $address = $request->address;
        if ($user && Schema::hasColumn('users', 'address') && empty($address)) {
            $address = $user->address;
        }

        return DB::transaction(function () use ($request, $service, $date, $time, $doctorId, $fullName, $durationMinutes, $user, $first, $middle, $last, $email, $contact, $address) {

            // ✅ If user typed contact/address and their profile is empty -> save to users table (one-time capture)
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

                if ($dirty) {
                    $user->save();
                }
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

            // ✅ store duration for overlap logic (if column exists)
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
            if (Schema::hasColumn('appointments', 'public_phone')) {
                $appointment->public_phone = $contact;
            }
            if (Schema::hasColumn('appointments', 'public_address')) {
                $appointment->public_address = $address;
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

            // ✅ ensure email is consistent with logged-in user
            if ($user && Schema::hasColumn('appointments', 'public_email')) {
                $appointment->public_email = $user->email;
            }

            $appointment->save();

            // ✅ SAME PAGE: redirect back to create with flash data
            return redirect()
                ->route('public.booking.create', $service->id)
                ->with('booking_success', true)
                ->with('success_appointment_id', $appointment->id);
        });
    }

    /**
     * Split a full name into first/middle/last (best-effort).
     */
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

    /**
     * ✅ TIME TRAP + OVERLAP SAFE SLOTS
     */
    private function computeSlots(string $date, ?int $doctorId, int $durationMinutes): array
    {
        $tz = config('app.timezone');

        // Sunday closed
        $day = Carbon::parse($date, $tz)->dayOfWeekIso; // 7 = Sunday
        if ($day === 7) return [];

        // clinic hours
        $openStart = Carbon::parse("$date 09:00", $tz);
        $openEnd   = Carbon::parse("$date 18:00", $tz);

        // settings
        $stepMinutes = 30;
        $leadMinutesToday = 60;

        if (!Schema::hasColumn('appointments', 'appointment_time')) return [];
        if (!Schema::hasColumn('appointments', 'appointment_date')) return [];

        // today -> remove past times + lead time
        $minStart = $openStart->copy();
        $now = now($tz);

        if ($openStart->isSameDay($now)) {
            $minCandidate = $now->copy()->addMinutes($leadMinutesToday);
            $minStart = $this->ceilToStep($minCandidate, $stepMinutes);
            if ($minStart->lt($openStart)) $minStart = $openStart->copy();
        }

        // build blocked intervals
        $bookedQuery = Appointment::query()->select(['appointment_time']);

        if (Schema::hasColumn('appointments', 'duration_minutes')) {
            $bookedQuery->addSelect('duration_minutes');
        }

        $bookedQuery->whereDate('appointment_date', $date);

        // ignore cancelled/declined/rejected
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

        // generate slots
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
