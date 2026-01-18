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

        return view('public.booking.create', [
            'service' => $service,
            'doctors' => $doctors,
            'successAppointment' => $successAppointment,
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

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],

            'doctor_id' => $doctorRequired
                ? ['required', 'integer', 'exists:doctors,id']
                : ['nullable', 'integer', 'exists:doctors,id'],

            'first_name'  => ['required', 'string', 'max:120'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'last_name'   => ['required', 'string', 'max:120'],

            'email'   => ['required', 'email', 'max:190'],
            'contact' => ['required', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],

            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $date = Carbon::parse($request->date)->toDateString();
        $time = $request->time;

        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        $durationMinutes = $this->serviceDurationMinutes($service);

        // ✅ TIME TRAP: re-check availability at submit time (overlap / past / too-close / closing)
        $available = in_array($time, $this->computeSlots($date, $doctorId, $durationMinutes), true);
        if (!$available) {
            return back()
                ->withErrors(['time' => 'That time slot is no longer available (overlap / too close / past / clinic closing). Please choose another.'])
                ->withInput();
        }

        $fullName = trim(
            $request->first_name . ' ' .
            ($request->middle_name ? trim($request->middle_name) . ' ' : '') .
            $request->last_name
        );

        return DB::transaction(function () use ($request, $service, $date, $time, $doctorId, $fullName, $durationMinutes) {

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
                $appointment->public_first_name = $request->first_name;
            }
            if (Schema::hasColumn('appointments', 'public_middle_name')) {
                $appointment->public_middle_name = $request->middle_name;
            }
            if (Schema::hasColumn('appointments', 'public_last_name')) {
                $appointment->public_last_name = $request->last_name;
            }

            if (Schema::hasColumn('appointments', 'public_email')) {
                $appointment->public_email = $request->email;
            }
            if (Schema::hasColumn('appointments', 'public_phone')) {
                $appointment->public_phone = $request->contact;
            }
            if (Schema::hasColumn('appointments', 'public_address')) {
                $appointment->public_address = $request->address;
            }
            if (Schema::hasColumn('appointments', 'public_message')) {
                $appointment->public_message = $request->message;
            }

            if (Schema::hasColumn('appointments', 'status')) {
                $appointment->status = 'pending';
            }

            if (auth()->check() && Schema::hasColumn('appointments', 'user_id')) {
                $appointment->user_id = auth()->id();
            }

            // recommended for consistent matching
            if (auth()->check() && Schema::hasColumn('appointments', 'public_email')) {
                $appointment->public_email = auth()->user()->email;
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
     * ✅ TIME TRAP + OVERLAP SAFE SLOTS
     * $durationMinutes comes from service.duration_minutes (fallback 60)
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

        // ✅ Time trap: today -> remove past times + lead time
        $minStart = $openStart->copy();
        $now = now($tz);

        if ($openStart->isSameDay($now)) {
            $minCandidate = $now->copy()->addMinutes($leadMinutesToday);
            $minStart = $this->ceilToStep($minCandidate, $stepMinutes);
            if ($minStart->lt($openStart)) $minStart = $openStart->copy();
        }

        // ✅ Build blocked intervals from existing appointments
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

        // ✅ Generate slot starts, excluding overlaps and closing overflow
        $slots = [];
        $cursor = $openStart->copy();

        while ($cursor->lt($openEnd)) {
            if ($cursor->lt($minStart)) {
                $cursor->addMinutes($stepMinutes);
                continue;
            }

            $candidateEnd = $cursor->copy()->addMinutes($durationMinutes);

            // must finish before closing
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
        // clamp to sane range
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
