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

        return view('public.booking.create', [
            'service' => $service,
            'doctors' => $doctors,
        ]);
    }

    public function slots(Request $request, Service $service)
    {
        $doctorRequired = $this->doctorRequired();

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'doctor_id' => $doctorRequired ? ['required', 'integer', 'exists:doctors,id'] : ['nullable', 'integer'],
        ]);

        $date = Carbon::parse($request->date)->toDateString();
        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        $slots = $this->computeSlots($date, $doctorId);

        return response()->json([
            'date' => $date,
            'doctor_id' => $doctorId,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request, Service $service)
    {
        $doctorRequired = $this->doctorRequired();

        // ✅ Booking form is SHORT: no birthdate / gender required
        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],

            'doctor_id' => $doctorRequired
                ? ['required', 'integer', 'exists:doctors,id']
                : ['nullable', 'integer', 'exists:doctors,id'],

            'first_name' => ['required', 'string', 'max:120'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],

            'email' => ['required', 'email', 'max:190'],
            'contact' => ['required', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],

            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $date = Carbon::parse($request->date)->toDateString();
        $time = $request->time;

        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        // prevent double booking
        $available = in_array($time, $this->computeSlots($date, $doctorId), true);
        if (!$available) {
            return back()
                ->withErrors(['time' => 'That time slot is no longer available. Please choose another.'])
                ->withInput();
        }

        $fullName = trim(
            $request->first_name . ' ' .
            ($request->middle_name ? trim($request->middle_name) . ' ' : '') .
            $request->last_name
        );

        return DB::transaction(function () use ($request, $service, $date, $time, $doctorId, $fullName) {

            $appointment = new Appointment();

            // service/date/time
            if (Schema::hasColumn('appointments', 'service_id')) $appointment->service_id = $service->id;
            if (Schema::hasColumn('appointments', 'appointment_date')) $appointment->appointment_date = $date;
            if (Schema::hasColumn('appointments', 'appointment_time')) $appointment->appointment_time = $time;

            // doctor (optional)
            if ($doctorId && Schema::hasColumn('appointments', 'doctor_id')) {
                $appointment->doctor_id = $doctorId;
            }

            if ($doctorId && Schema::hasColumn('appointments', 'dentist_name')) {
                $appointment->dentist_name = Doctor::whereKey($doctorId)->value('name');
            }

            // ✅ public fields (used for approval + future linking)
            if (Schema::hasColumn('appointments', 'public_name')) $appointment->public_name = $fullName;

            if (Schema::hasColumn('appointments', 'public_first_name')) $appointment->public_first_name = $request->first_name;
            if (Schema::hasColumn('appointments', 'public_middle_name')) $appointment->public_middle_name = $request->middle_name;
            if (Schema::hasColumn('appointments', 'public_last_name')) $appointment->public_last_name = $request->last_name;

            if (Schema::hasColumn('appointments', 'public_email')) $appointment->public_email = $request->email;
            if (Schema::hasColumn('appointments', 'public_phone')) $appointment->public_phone = $request->contact;
            if (Schema::hasColumn('appointments', 'public_address')) $appointment->public_address = $request->address;
            if (Schema::hasColumn('appointments', 'public_message')) $appointment->public_message = $request->message;

            if (Schema::hasColumn('appointments', 'status')) $appointment->status = 'pending';

            $appointment->save();

            // ✅ THIS is what fixes your error: route calls success() now exists
            return redirect()->route('public.booking.success', $appointment);
        });
    }

    // ✅ REQUIRED by your route: public.booking.success
    public function success(Appointment $appointment)
    {
        $appointment->loadMissing(['service', 'doctor']);

        return view('public.booking.success', compact('appointment'));
    }

    private function computeSlots(string $date, ?int $doctorId): array
    {
        $day = Carbon::parse($date)->dayOfWeekIso;
        if ($day === 7) return []; // Sunday closed

        $start = Carbon::parse($date . ' 09:00');
        $end   = Carbon::parse($date . ' 18:00');
        $stepMinutes = 30;

        if (!Schema::hasColumn('appointments', 'appointment_time')) return [];

        $bookedQuery = Appointment::query();

        if (Schema::hasColumn('appointments', 'appointment_date')) {
            $bookedQuery->whereDate('appointment_date', $date);
        }

        if (Schema::hasColumn('appointments', 'status')) {
            $bookedQuery->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['cancelled', 'canceled', 'declined', 'rejected']);
            });
        }

        if ($doctorId && Schema::hasColumn('appointments', 'doctor_id')) {
            $bookedQuery->where('doctor_id', $doctorId);
        }

        $booked = $bookedQuery->pluck('appointment_time')->map(function ($t) {
            try {
                return Carbon::parse($t)->format('H:i');
            } catch (\Throwable $e) {
                return (string) $t;
            }
        })->unique()->values()->all();

        $slots = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $label = $cursor->format('H:i');
            if (!in_array($label, $booked, true)) $slots[] = $label;
            $cursor->addMinutes($stepMinutes);
        }

        return $slots;
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
