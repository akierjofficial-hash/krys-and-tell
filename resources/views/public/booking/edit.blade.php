@extends('layouts.kt-public')
@section('title', 'Edit Booking | ' . ($service->name ?? 'Service'))

@section('content')
@php
    $doctorName = optional($appointment->doctor)->name ?? ($appointment->dentist_name ?? null);
    $doctorRestrictionEnabled = $doctorRestrictionEnabled ?? false;
    $autoAssignedDoctorId = $autoAssignedDoctorId ?? null;
@endphp

<section class="kt-booking-page-v2">
    <div class="kt-page-shell">
        <a href="{{ route('profile.show') }}" class="kt-back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/>
            </svg>
            Back to Profile
        </a>

        <div class="kt-booking-v2__grid">
            <article class="kt-booking-v2__card">
                <div class="kt-booking-v2__head">
                    <div>
                        <div class="kt-label">Pending Appointment</div>
                        <h1 class="kt-section-title">Edit Booking<br><em>{{ $service->name ?? 'Service' }}</em></h1>
                        <p class="kt-section-body">Update your preferred dentist, date, and time while your request is still pending.</p>
                    </div>
                    <span class="kt-booking-v2__pill">Pending</span>
                </div>

                @if(session('success'))
                    <div class="kt-form-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="kt-form-error-banner">
                        <strong>Unable to update booking:</strong>
                        <ul><li>{{ session('error') }}</li></ul>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="kt-form-error-banner">
                        <strong>Please fix the following:</strong>
                        <ul>
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.booking.update', $appointment->id) }}" class="kt-booking-v2__form" id="ktPublicBookingEditForm">
                    @csrf
                    @method('PUT')

                    @if($doctorRequired)
                        <div class="kt-form-group">
                            <label class="kt-form-label" for="doctor_id">Doctor</label>
                            <select class="kt-form-input @error('doctor_id') kt-input--error @enderror"
                                name="doctor_id"
                                id="doctor_id"
                                data-auto-doctor-id="{{ $autoAssignedDoctorId }}"
                                data-doctor-restricted="{{ $doctorRestrictionEnabled ? '1' : '0' }}"
                                required>
                                <option value="">Choose doctor</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}" @selected((string)old('doctor_id', $prefillDoctorId) === (string)$d->id)>
                                        {{ $d->name ?? ('Doctor #' . $d->id) }}
                                    </option>
                                @endforeach
                            </select>
                            <span id="doctorHelp" class="kt-form-help">
                                @if($doctorRestrictionEnabled && $doctors->isEmpty())
                                    No active dentist is currently assigned to this treatment.
                                @endif
                            </span>
                            @error('doctor_id')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>
                    @else
                        <input type="hidden" name="doctor_id" id="doctor_id" value="{{ old('doctor_id', $prefillDoctorId) }}">
                    @endif

                    <div class="kt-form-row">
                        <div class="kt-form-group">
                            <label class="kt-form-label" for="date">Date</label>
                            <input type="date"
                                class="kt-form-input @error('date') kt-input--error @enderror"
                                name="date"
                                id="date"
                                value="{{ old('date', $prefillDate) }}"
                                min="{{ now()->toDateString() }}"
                                required>
                            @error('date')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>

                        @if(!$isWalkIn)
                            <div class="kt-form-group">
                                <label class="kt-form-label" for="time">Time</label>
                                <select class="kt-form-input @error('time') kt-input--error @enderror" name="time" id="time" required>
                                    <option value="">{{ $doctorRequired ? 'Select doctor and date first...' : 'Select date first...' }}</option>
                                </select>

                                <div id="slotGrid" class="kt-booking-v2__slot-grid"></div>
                                <span id="timeHelp" class="kt-form-help"></span>
                                @error('time')<span class="kt-form-error">{{ $message }}</span>@enderror
                            </div>
                        @else
                            <input type="hidden" name="time" value="">
                        @endif
                    </div>

                    <div class="kt-form-group">
                        <label class="kt-form-label">Message (optional)</label>
                        <textarea class="kt-form-input @error('message') kt-input--error @enderror" name="message" rows="3" maxlength="500" placeholder="Anything you want us to know?">{{ old('message', $appointment->public_message ?? '') }}</textarea>
                        @error('message')<span class="kt-form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="kt-booking-v2__actions kt-booking-v2__actions--form">
                        <button type="submit" class="kt-btn-primary"><span>Save Changes</span></button>
                        <a class="kt-btn-ghost" href="{{ route('profile.show') }}">My Profile ></a>
                    </div>
                </form>
            </article>

            <aside class="kt-booking-v2__aside">
                <div class="kt-booking-v2__photo">
                    <img src="{{ asset('images/pic2.jpg') }}" alt="" loading="lazy">
                </div>

                <article class="kt-booking-v2__aside-card">
                    <h3>Current Request</h3>
                    <div class="kt-booking-v2__meta">
                        <article>
                            <strong>Service</strong>
                            <span>{{ $service->name ?? '-' }}</span>
                        </article>
                        <article>
                            <strong>Doctor</strong>
                            <span>{{ $doctorName ?: '-' }}</span>
                        </article>
                        <article>
                            <strong>Date</strong>
                            <span>{{ $appointment->appointment_date ?? '-' }}</span>
                        </article>
                        <article>
                            <strong>Time</strong>
                            <span>
                                @if(!empty($appointment->appointment_time))
                                    {{ $appointment->appointment_time }}
                                @elseif(!empty($appointment->is_walk_in_request))
                                    WALK-IN REQUEST
                                @else
                                    WALK-IN
                                @endif
                            </span>
                        </article>
                    </div>
                </article>

                <article class="kt-booking-v2__aside-card">
                    <h3>Update Guidelines</h3>
                    <ul>
                        <li>Only pending bookings can be edited.</li>
                        <li>Changing dentist or date refreshes available slots.</li>
                        <li>Confirmed appointments require staff assistance to change.</li>
                    </ul>
                </article>

                @if($doctorRestrictionEnabled)
                    <article class="kt-booking-v2__aside-card">
                        <h3>Treatment Assignment</h3>
                        <p>
                            This treatment is tied to clinic-assigned dentists.
                            @if($autoAssignedDoctorId && $doctors->firstWhere('id', $autoAssignedDoctorId))
                                {{ $doctors->firstWhere('id', $autoAssignedDoctorId)->name }} is automatically assigned when available.
                            @elseif($doctors->count() > 1)
                                Only the dentists assigned to this treatment can be selected.
                            @else
                                Staff needs to assign a dentist before this request can be updated online.
                            @endif
                        </p>
                    </article>
                @endif
            </aside>
        </div>
    </div>
</section>

@if(!$isWalkIn)
@push('scripts')
<script>
(function(){
    'use strict';

    var serviceId = @json($service->id);
    var dateEl = document.getElementById('date');
    var timeEl = document.getElementById('time');
    var helpEl = document.getElementById('timeHelp');
    var doctorEl = document.getElementById('doctor_id');
    var doctorHelpEl = document.getElementById('doctorHelp');
    var gridEl = document.getElementById('slotGrid');
    var doctorRequired = @json((bool) $doctorRequired);
    var oldTime = @json(old('time', $prefillTime));
    var seededOldTime = false;
    var seededOldDoctor = false;
    var initialAutoDoctorId = doctorEl ? String(doctorEl.getAttribute('data-auto-doctor-id') || '') : '';
    var treatmentRestricted = doctorEl ? doctorEl.getAttribute('data-doctor-restricted') === '1' : false;

    if (!dateEl || !timeEl) return;

    function fmt12h(t){
        if (!t || typeof t !== 'string' || t.indexOf(':') === -1) return t;
        var parts = t.split(':');
        var h = parseInt(parts[0], 10);
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = (h % 12) || 12;
        return h + ':' + parts[1] + ' ' + ampm;
    }

    function setLoading(msg){
        timeEl.disabled = false;
        timeEl.innerHTML = '<option value="">' + msg + '</option>';
        if (helpEl) helpEl.textContent = '';
        if (gridEl) gridEl.innerHTML = '';
    }

    function resetDoctorOptions(){
        if (!doctorEl) return;
        doctorEl.innerHTML = '<option value="">Choose doctor</option>';
        if (doctorHelpEl) doctorHelpEl.textContent = '';
    }

    function optionLabel(info, unavailable){
        var label = info && info.name ? info.name : 'Doctor';
        return unavailable ? label + ' (Unavailable)' : label;
    }

    function populateDoctors(payload, preferredDoctorId){
        if (!doctorEl) return;

        resetDoctorOptions();

        var doctors = Array.isArray(payload && payload.doctors) ? payload.doctors : [];
        var meta = payload && payload.meta ? payload.meta : {};
        var preferredId = preferredDoctorId ? String(preferredDoctorId) : '';
        var autoDoctorId = meta.auto_assigned_doctor_id ? String(meta.auto_assigned_doctor_id) : initialAutoDoctorId;
        var autoDoctorName = meta.auto_assigned_doctor_name || '';
        var bookingBlocked = !!meta.booking_blocked;
        var bookingBlockedReason = meta.booking_blocked_reason || '';
        var availableCount = 0;
        var autoDoctorAvailable = false;
        var autoDoctorReason = '';

        doctors.forEach(function(info){
            if (!info || !info.id) return;

            var doctorId = String(info.id);
            var isAuto = autoDoctorId && doctorId === autoDoctorId;
            var unavailable = info.available === false;
            var option = document.createElement('option');
            option.value = doctorId;
            option.textContent = optionLabel(info, unavailable);

            if (unavailable && !isAuto) {
                option.disabled = true;
            } else {
                availableCount += 1;
            }

            if (isAuto) {
                autoDoctorAvailable = !unavailable;
                autoDoctorReason = info.reason || '';
            }

            doctorEl.appendChild(option);
        });

        doctorRequired = !!meta.doctor_required;
        doctorEl.required = doctorRequired;

        if (bookingBlocked) {
            doctorEl.innerHTML = '<option value="">' + (bookingBlockedReason || 'No dentist available for this treatment') + '</option>';
            if (doctorHelpEl) {
                doctorHelpEl.textContent = bookingBlockedReason;
            }
            return;
        }

        if (autoDoctorId) {
            doctorEl.value = autoDoctorId;
            if (doctorHelpEl) {
                doctorHelpEl.textContent = autoDoctorName
                    ? autoDoctorName + (autoDoctorAvailable
                        ? ' is automatically assigned to this treatment.'
                        : ' is assigned to this treatment but unavailable on the selected date. ' + autoDoctorReason)
                    : '';
            }
            seededOldDoctor = true;
            return;
        }

        if (preferredId && doctorEl.querySelector('option[value="' + preferredId + '"]:not([disabled])')) {
            doctorEl.value = preferredId;
            seededOldDoctor = true;
        } else if (!seededOldDoctor && doctorEl.querySelector('option[value="' + @json((string) old('doctor_id', $prefillDoctorId)) + '"]:not([disabled])')) {
            doctorEl.value = @json((string) old('doctor_id', $prefillDoctorId));
            seededOldDoctor = true;
        }

        if (doctorHelpEl) {
            if (treatmentRestricted && doctors.length > 0) {
                doctorHelpEl.textContent = 'Only dentists assigned to this treatment are available.';
            } else if (availableCount === 0 && doctorRequired) {
                doctorHelpEl.textContent = 'No dentist is available on this date.';
            } else {
                doctorHelpEl.textContent = '';
            }
        }
    }

    async function syncDoctorsByDate(date, preferredDoctorId){
        if (!doctorRequired || !doctorEl) return;
        if (!date) {
            resetDoctorOptions();
            return;
        }

        var res;
        try {
            var url = new URL('/book/' + serviceId + '/doctors', window.location.origin);
            url.searchParams.set('date', date);
            res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        } catch (e) {
            resetDoctorOptions();
            return;
        }

        if (!res.ok) {
            resetDoctorOptions();
            return;
        }

        populateDoctors(await res.json(), preferredDoctorId);
    }

    function renderGrid(slots){
        if (!gridEl) return;
        gridEl.innerHTML = slots.map(function(t){
            return '<button type="button" class="kt-booking-v2__slot" data-time="' + t + '">' + fmt12h(t) + '</button>';
        }).join('');

        var btns = Array.from(gridEl.querySelectorAll('.kt-booking-v2__slot'));
        function markActive(val){
            btns.forEach(function(btn){
                btn.classList.toggle('is-active', btn.dataset.time === val);
            });
        }

        markActive(timeEl.value);
        btns.forEach(function(btn){
            btn.addEventListener('click', function(){
                timeEl.value = btn.dataset.time;
                markActive(btn.dataset.time);
            });
        });
    }

    async function loadSlots(){
        var date = dateEl.value;
        var preferredDoctorId = doctorEl ? doctorEl.value : '';
        await syncDoctorsByDate(date, preferredDoctorId);
        var doctorId = doctorEl ? doctorEl.value : '';
        var previousValue = timeEl.value;

        if (doctorRequired && doctorEl && !doctorId) {
            setLoading('Select an available dentist first...');
            return;
        }
        if (!date) {
            setLoading('Select date first...');
            return;
        }

        setLoading('Loading available times...');
        var url = new URL('/book/' + serviceId + '/slots', window.location.origin);
        url.searchParams.set('date', date);
        if (doctorRequired) url.searchParams.set('doctor_id', doctorId);

        var res;
        try {
            res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
        } catch (e) {
            setLoading('Unable to load slots');
            return;
        }

        if (!res.ok) {
            setLoading('Unable to load slots');
            return;
        }

        var data = await res.json();
        var slots = data.slots || [];

        if (!slots.length) {
            timeEl.disabled = true;
            timeEl.innerHTML = '<option value="">No available slots</option>';
            var doctorUnavailable = Boolean(data && data.meta && data.meta.doctor_unavailable);
            var bookingBlocked = Boolean(data && data.meta && data.meta.booking_blocked);
            var doctorUnavailableReason = (data && data.meta && data.meta.doctor_unavailable_reason) || 'Unavailable on this date.';
            if (helpEl) {
                helpEl.textContent = bookingBlocked
                    ? ((data.meta && data.meta.booking_blocked_reason) || 'No dentist available for this treatment.')
                    : doctorUnavailable
                    ? 'Selected dentist is unavailable: ' + doctorUnavailableReason
                    : 'No slots available.';
            }
            if (gridEl) gridEl.innerHTML = '';
            return;
        }

        timeEl.disabled = false;
        timeEl.innerHTML = '<option value="">Select time...</option>' + slots.map(function(t){
            var selected = (!seededOldTime && oldTime && oldTime === t) ? 'selected' : '';
            return '<option value="' + t + '" ' + selected + '>' + fmt12h(t) + '</option>';
        }).join('');

        if (!seededOldTime && oldTime && slots.indexOf(oldTime) !== -1) {
            timeEl.value = oldTime;
            seededOldTime = true;
        } else if (previousValue && slots.indexOf(previousValue) !== -1) {
            timeEl.value = previousValue;
        }

        renderGrid(slots);
        if (helpEl) helpEl.textContent = slots.length + ' slot(s) available.';
    }

    dateEl.addEventListener('change', loadSlots);
    if (doctorEl) doctorEl.addEventListener('change', loadSlots);

    if (dateEl.value && (!doctorRequired || (doctorEl && doctorEl.value))) {
        loadSlots();
    } else {
        setLoading(doctorRequired ? 'Select dentist and date first...' : 'Select date first...');
        if (doctorEl && initialAutoDoctorId && doctorEl.value === initialAutoDoctorId) {
            loadSlots();
        }
    }
})();
</script>
@endpush
@endif
@endsection
