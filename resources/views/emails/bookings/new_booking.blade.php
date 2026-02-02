@component('mail::message')
# New Booking Submitted ✅

**Patient:** {{ $appointment->public_name ?? trim(($appointment->public_first_name ?? '').' '.($appointment->public_last_name ?? '')) ?? '—' }}

**Service:** {{ $appointment->service->name ?? '—' }}

**Date:** {{ $appointment->appointment_date ?? '—' }}

**Time:** {{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : 'WALK-IN' }}

**Status:** {{ ucfirst($appointment->status ?? 'pending') }}

@component('mail::button', ['url' => route('staff.appointments.show', $appointment->id)])
View Appointment
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
