<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorUnavailability;
use App\Models\Patient;
use App\Notifications\AppointmentApproved;
use App\Notifications\AppointmentDeclined;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class BookingApprovalService
{
    private const BLOCKING = ['upcoming', 'approved', 'confirmed', 'scheduled'];
    private const CHAIRS = 2;

    public function __construct(private AdminAuditService $audit) {}

    public function approve(Appointment $source, array $input, $actor = null): Appointment
    {
        $notify = false;
        $appointment = DB::transaction(function () use ($source, $input, $actor, &$notify) {
            $appointment = Appointment::with(['user', 'service', 'doctor'])->lockForUpdate()->findOrFail($source->id);
            $this->assertPending($appointment);

            $service = $appointment->service;
            $walkIn = (bool) $appointment->is_walk_in_request
                || ($service && is_numeric($service->duration_minutes) && (int) $service->duration_minutes <= 5);
            $original = [
                'appointment_date' => $this->date($appointment->appointment_date),
                'appointment_time' => $this->time($appointment->appointment_time),
                'doctor_id' => $appointment->doctor_id ? (int) $appointment->doctor_id : null,
            ];
            $final = [
                'appointment_date' => !empty($input['appointment_date']) ? Carbon::parse($input['appointment_date'])->toDateString() : $original['appointment_date'],
                'appointment_time' => $walkIn ? null : (!empty($input['appointment_time']) ? Carbon::parse($input['appointment_time'])->format('H:i') : $original['appointment_time']),
                'doctor_id' => array_key_exists('doctor_id', $input) ? ($input['doctor_id'] ? (int) $input['doctor_id'] : null) : $original['doctor_id'],
            ];

            if (!$final['appointment_date']) $this->fail('appointment_date', 'Set an appointment date before approving.');
            if (Doctor::active()->exists() && !$final['doctor_id']) $this->fail('doctor_id', 'Select a dentist before approving.');
            if (!$walkIn && !$final['appointment_time']) $this->fail('appointment_time', 'Select a time before approving.');

            $changed = $original !== $final;
            $note = trim((string) ($input['staff_note'] ?? ''));
            if ($changed && $note === '') $this->fail('staff_note', 'Add a reason when changing the dentist, date, or time.');

            if ($final['doctor_id']) $this->assertDentistEligible($appointment, $final['doctor_id']);
            if (!$walkIn) $this->assertSlotAvailable($appointment, $final);

            if (!$appointment->patient_id) $appointment->patient_id = $this->resolvePatient($appointment);
            $appointment->appointment_date = $final['appointment_date'];
            $appointment->appointment_time = $final['appointment_time'];
            $appointment->doctor_id = $final['doctor_id'];
            $appointment->dentist_name = $final['doctor_id'] ? Doctor::whereKey($final['doctor_id'])->value('name') : null;
            $appointment->staff_note = $note ?: null;
            if (!$walkIn && !$appointment->duration_minutes) $appointment->duration_minutes = 60;
            $appointment->status = $appointment->is_walk_in_request ? 'walked_in' : 'upcoming';
            $appointment->save();
            $notify = true;

            if ($actor) $this->audit->record($actor, 'booking.approved', $appointment, 'Booking request approved.', $original,
                $final + ['status' => $appointment->status], $note ?: null, $changed);
            return $appointment;
        });

        if ($notify) $this->notify($appointment->fresh(['user', 'service', 'doctor']), new AppointmentApproved($appointment));
        return $appointment->fresh(['user', 'service', 'doctor']);
    }

    public function decline(Appointment $source, string $reason, $actor = null): Appointment
    {
        $reason = trim($reason);
        if ($reason === '') $this->fail('staff_note', 'Enter a reason for declining this request.');

        $appointment = DB::transaction(function () use ($source, $reason, $actor) {
            $appointment = Appointment::with(['user', 'service', 'doctor'])->lockForUpdate()->findOrFail($source->id);
            $this->assertPending($appointment);
            $appointment->staff_note = $reason;
            $appointment->status = 'declined';
            $appointment->save();
            if ($actor) $this->audit->record($actor, 'booking.declined', $appointment, 'Booking request declined.',
                ['status' => 'pending'], ['status' => 'declined'], $reason, true);
            return $appointment;
        });

        $this->notify($appointment, new AppointmentDeclined($appointment));
        return $appointment;
    }

    private function assertPending(Appointment $appointment): void
    {
        if ($appointment->status !== 'pending') $this->fail('appointment', 'This booking request has already been processed.');
    }

    private function assertDentistEligible(Appointment $appointment, int $doctorId): void
    {
        $doctor = Doctor::active()->find($doctorId);
        if (!$doctor) $this->fail('doctor_id', 'The selected dentist is unavailable.');
        if ($appointment->service?->restrict_to_assigned_doctors
            && !$appointment->service->assignedDoctors()->whereKey($doctorId)->exists()) {
            $this->fail('doctor_id', 'The selected dentist is not assigned to this treatment.');
        }
    }

    private function assertSlotAvailable(Appointment $appointment, array $slot): void
    {
        $date = $slot['appointment_date'];
        $time = $slot['appointment_time'];
        $doctor = $slot['doctor_id'] ? Doctor::find($slot['doctor_id']) : null;
        $day = Carbon::parse($date)->dayOfWeekIso;
        $working = $doctor?->working_days ?: [1, 2, 3, 4, 5, 6];
        if ($doctor && !in_array($day, array_map('intval', $working), true)) $this->fail('appointment_date', 'The selected dentist does not work on that date.');
        if ($doctor && DoctorUnavailability::where('doctor_id', $doctor->id)->whereDate('unavailable_date', $date)->exists()) {
            $this->fail('appointment_date', 'The selected dentist is unavailable on that date.');
        }
        $start = Carbon::parse("$date $time", config('app.timezone'));
        $open = Carbon::parse("$date " . ($doctor?->work_start_time ?: '09:00'), config('app.timezone'));
        $close = Carbon::parse("$date " . ($doctor?->work_end_time ?: '17:00'), config('app.timezone'));
        if ($start->lt($open) || $start->copy()->addHour()->gt($close)) $this->fail('appointment_time', 'That time is outside the dentist schedule.');
        if ($start->isToday() && $start->lt(now(config('app.timezone'))->addHour())) $this->fail('appointment_time', 'That time does not meet the one-hour booking lead time.');

        $blocking = Appointment::whereDate('appointment_date', $date)->whereIn('status', self::BLOCKING)
            ->whereKeyNot($appointment->id)->lockForUpdate()->get();
        $candidateEnd = $start->copy()->addHour();
        $overlapping = $blocking->filter(function ($row) use ($date, $start, $candidateEnd) {
            if (!$row->appointment_time) return false;
            $bookedStart = Carbon::parse($date.' '.$row->appointment_time, config('app.timezone'));
            $bookedEnd = $bookedStart->copy()->addMinutes(max(1, (int) ($row->duration_minutes ?: 60)));
            return $start->lt($bookedEnd) && $candidateEnd->gt($bookedStart);
        });
        if ($overlapping->count() >= self::CHAIRS || ($slot['doctor_id'] && $overlapping->contains(fn ($row) => (int) $row->doctor_id === $slot['doctor_id']))) {
            $this->fail('appointment_time', 'That time slot is no longer available. Choose another time.');
        }
    }

    private function resolvePatient(Appointment $appointment): int
    {
        $email = trim((string) ($appointment->public_email ?: $appointment->user?->email));
        $phone = trim((string) $appointment->public_phone);
        if ($email !== '') {
            $matches = Patient::whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->get();
            if ($matches->count() === 1) return $matches->first()->id;
        }
        if ($phone !== '') {
            $matches = Patient::where('contact_number', $phone)->get();
            if ($matches->count() === 1) return $matches->first()->id;
        }

        $first = trim((string) $appointment->public_first_name);
        $middle = trim((string) $appointment->public_middle_name);
        $last = trim((string) $appointment->public_last_name);
        if (($first === '' || $last === '') && $appointment->public_name) {
            $parts = preg_split('/\s+/', trim($appointment->public_name));
            $first = $first ?: (string) array_shift($parts);
            $last = $last ?: (string) array_pop($parts);
            $middle = $middle ?: implode(' ', $parts);
        }
        if ($first === '' || $last === '') $this->fail('appointment', 'Complete the patient first and last name before approval.');

        return Patient::create([
            'first_name' => $first,
            'middle_name' => $middle ?: null,
            'last_name' => $last,
            'email' => $email ?: null,
            'contact_number' => $phone ?: null,
            'address' => $appointment->public_address ?: null,
            'gender' => $appointment->public_gender ?: null,
            'birthdate' => $appointment->public_birthdate ?: null,
        ])->id;
    }

    private function notify(Appointment $appointment, object $notification): void
    {
        if ($appointment->user) $appointment->user->notify($notification);
        elseif ($appointment->public_email) Notification::route('mail', $appointment->public_email)->notify($notification);
    }

    private function date($value): ?string { return $value ? Carbon::parse($value)->toDateString() : null; }
    private function time($value): ?string { return $value ? Carbon::parse($value)->format('H:i') : null; }
    private function fail(string $field, string $message): never { throw ValidationException::withMessages([$field => $message]); }
}
