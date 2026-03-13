@extends('layouts.kt-public')
@section('title', 'Edit Booking | ' . ($service->name ?? 'Service'))

@section('content')
@php
    $doctorName = optional($appointment->doctor)->name ?? ($appointment->dentist_name ?? null);
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
                            <select class="kt-form-input @error('doctor_id') kt-input--error @enderror" name="doctor_id" id="doctor_id" required>
                                <option value="">Choose doctor</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}" @selected((string)old('doctor_id', $prefillDoctorId) === (string)$d->id)>
                                        {{ $d->name ?? ('Doctor #' . $d->id) }}
                                    </option>
                                @endforeach
                            </select>
                            <span id="doctorHelp" class="kt-form-help"></span>
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
    var doctorLabelMap = new Map();

    if (!dateEl || !timeEl) return;

    if (doctorEl) {
        Array.from(doctorEl.options).forEach(function(opt){
            if (!opt.value) return;
            doctorLabelMap.set(String(opt.value), (opt.textContent || '').trim());
        });
    }

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
        Array.from(doctorEl.options).forEach(function(opt){
            if (!opt.value) return;
            var key = String(opt.value);
            var baseLabel = doctorLabelMap.get(key) || (opt.textContent || '').replace(/\s+\(Unavailable\)\s*$/i, '').trim();
            doctorLabelMap.set(key, baseLabel);
            opt.textContent = baseLabel;
            opt.disabled = false;
            opt.hidden = false;
        });
        if (doctorHelpEl) doctorHelpEl.textContent = '';
    }

    async function syncDoctorsByDate(date){
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

        var data = await res.json();
        var doctors = Array.isArray(data && data.doctors) ? data.doctors : [];
        var byId = new Map(doctors.map(function(d){ return [String(d.id), d]; }));
        var selectedUnavailableReason = '';

        Array.from(doctorEl.options).forEach(function(opt){
            if (!opt.value) return;
            var key = String(opt.value);
            var info = byId.get(key);
            var baseLabel = doctorLabelMap.get(key) || (opt.textContent || '').replace(/\s+\(Unavailable\)\s*$/i, '').trim();
            doctorLabelMap.set(key, baseLabel);

            if (!info || info.available) {
                opt.textContent = baseLabel;
                opt.disabled = false;
                opt.hidden = false;
                return;
            }

            opt.textContent = baseLabel + ' (Unavailable)';
            opt.disabled = true;
            opt.hidden = true;

            if (doctorEl.value === key) {
                selectedUnavailableReason = info.reason || 'Unavailable on this date.';
            }
        });

        if (doctorEl.value) {
            var selectedInfo = byId.get(String(doctorEl.value));
            if (selectedInfo && !selectedInfo.available) {
                doctorEl.value = '';
            }
        }

        if (doctorHelpEl) {
            doctorHelpEl.textContent = selectedUnavailableReason
                ? 'Selected dentist is unavailable: ' + selectedUnavailableReason
                : '';
        }
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
        await syncDoctorsByDate(date);
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
            var doctorUnavailableReason = (data && data.meta && data.meta.doctor_unavailable_reason) || 'Unavailable on this date.';
            if (helpEl) {
                helpEl.textContent = doctorUnavailable
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
    }
})();
</script>
@endpush
@endif
@endsection
