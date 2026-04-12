@extends('layouts.kt-public')
@section('title', 'Book | ' . $service->name)

@section('content')
@php
    $u = auth()->user();

    $recentBookingName = null;
    if ($u && \Illuminate\Support\Facades\Schema::hasTable('appointments')) {
        $aq = \App\Models\Appointment::query();

        if (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'user_id')) {
            $aq->where('user_id', $u->id);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'public_email') && !empty($u->email)) {
            $aq->where('public_email', $u->email);
        }

        $lastAppt = $aq->orderByDesc('id')->first();
        if ($lastAppt) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'public_name') && !empty($lastAppt->public_name)) {
                $recentBookingName = $lastAppt->public_name;
            } else {
                $f = (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'public_first_name') ? ($lastAppt->public_first_name ?? '') : '');
                $m = (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'public_middle_name') ? ($lastAppt->public_middle_name ?? '') : '');
                $l = (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'public_last_name') ? ($lastAppt->public_last_name ?? '') : '');
                $rebuilt = trim(implode(' ', array_filter([trim($f), trim($m), trim($l)])));
                if ($rebuilt !== '') {
                    $recentBookingName = $rebuilt;
                }
            }
        }
    }

    $fullName = trim(old('full_name', $recentBookingName ?? ($u->name ?? '')));
    $email = trim(old('email', $u->email ?? ''));

    $isWalkIn = $isWalkIn ?? (function () use ($service) {
        $durRaw = $service->duration_minutes ?? null;
        if ($durRaw === null || $durRaw === '') {
            return true;
        }
        if (is_numeric($durRaw)) {
            $d = (int) $durRaw;
            return $d > 0 && $d <= 5;
        }
        return false;
    })();

    $parts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);
    $first = $parts[0] ?? '';
    $last = count($parts) > 1 ? $parts[count($parts)-1] : ($parts[0] ?? '');
    $middle = (count($parts) > 2) ? implode(' ', array_slice($parts, 1, -1)) : '';
    if ($first === '' && $fullName !== '') {
        $first = $fullName;
    }
    if ($last === '' && $first !== '') {
        $last = $first;
    }

    $doctorRequired = $doctorRequired ?? ($doctors->count() > 0);
    $doctorRestrictionEnabled = $doctorRestrictionEnabled ?? false;
    $autoAssignedDoctorId = $autoAssignedDoctorId ?? null;
    $hasSuccess = session('booking_success') && !empty($successAppointment);

    $needsDetails = $needsDetails ?? true;
    $profile = $profile ?? ['contact' => null, 'address' => null, 'birthdate' => null];

    $contactVal = old('contact', $profile['contact'] ?? ($u->phone_number ?? ''));
    $addressVal = old('address', $profile['address'] ?? ($u->address ?? ''));

    $birthdateVal = old('birthdate');
    if (!$birthdateVal && !empty($profile['birthdate'])) {
        try { $birthdateVal = \Carbon\Carbon::parse($profile['birthdate'])->format('Y-m-d'); } catch (\Throwable $e) {}
    }
    if (!$birthdateVal && !empty($u->birthdate)) {
        try { $birthdateVal = \Carbon\Carbon::parse($u->birthdate)->format('Y-m-d'); } catch (\Throwable $e) {}
    }
@endphp

<section class="kt-booking-page-v2">
    <div class="kt-page-shell">
        <a href="{{ route('public.services.show', $service->id) }}" class="kt-back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/>
            </svg>
            Back to Service
        </a>

        @if($hasSuccess)
            @php
                $status = strtolower((string) ($successAppointment->status ?? ''));
                $canEditPending = $status === 'pending';
            @endphp
            <article class="kt-booking-v2__card kt-booking-v2__success">
                <div class="kt-booking-v2__head">
                    <div>
                        <div class="kt-label">Booking Status</div>
                        <h1 class="kt-section-title">Booking <em>{{ session('booking_updated') ? 'Updated' : 'Submitted' }}</em></h1>
                        <p class="kt-section-body">Your request is saved as pending. Our team will review and confirm by email.</p>
                    </div>
                    <span class="kt-booking-v2__pill">Pending</span>
                </div>

                <div class="kt-booking-v2__summary">
                    <article>
                        <strong>Service</strong>
                        <span>{{ $successAppointment->service->name ?? '-' }}</span>
                    </article>
                    <article>
                        <strong>Doctor</strong>
                        <span>{{ optional($successAppointment->doctor)->name ?? ($successAppointment->dentist_name ?? '-') }}</span>
                    </article>
                    <article>
                        <strong>Date</strong>
                        <span>{{ $successAppointment->appointment_date ?? '-' }}</span>
                    </article>
                    <article>
                        <strong>Time</strong>
                        <span>
                            @if(!empty($successAppointment->appointment_time))
                                {{ $successAppointment->appointment_time }}
                            @elseif(!empty($successAppointment->is_walk_in_request))
                                WALK-IN REQUEST
                            @else
                                WALK-IN
                            @endif
                        </span>
                    </article>
                </div>

                <div class="kt-booking-v2__actions">
                    @if($canEditPending)
                        <a class="kt-btn-ghost" href="{{ route('public.booking.edit', $successAppointment->id) }}">Edit Booking</a>
                    @endif
                    <a class="kt-btn-primary" href="{{ route('public.services.index') }}"><span>Book Another Service</span></a>
                    <a class="kt-btn-ghost" href="{{ route('profile.show') }}">Go to My Profile</a>
                </div>
            </article>
        @else
            <div class="kt-booking-v2__grid">
                <article class="kt-booking-v2__card">
                    <div class="kt-booking-v2__head">
                        <div>
                            <div class="kt-label">Appointment Form</div>
                            <h1 class="kt-section-title">Book <em>{{ $service->name }}</em></h1>
                            <p class="kt-section-body">Choose your preferred schedule and submit your booking request.</p>
                        </div>
                        <span class="kt-booking-v2__pill">{{ $isWalkIn ? 'Walk-in Service' : 'Scheduled Service' }}</span>
                    </div>

                    @if ($errors->any())
                        <div class="kt-form-error-banner">
                            <strong>Please fix the following:</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public.booking.store', $service->id) }}" id="ktPublicBookingForm" class="kt-booking-v2__form">
                        @csrf

                        <div class="kt-form-group">
                            <label class="kt-form-label" for="full_name">Full Name</label>
                            <input
                                id="full_name"
                                type="text"
                                name="full_name"
                                class="kt-form-input @error('full_name') kt-input--error @enderror"
                                value="{{ old('full_name', $fullName) }}"
                                placeholder="Your full name"
                                required
                            >
                            @error('full_name')<span class="kt-form-error">{{ $message }}</span>@enderror
                            <input type="hidden" name="first_name" value="{{ old('first_name', $first) }}">
                            <input type="hidden" name="middle_name" value="{{ old('middle_name', $middle) }}">
                            <input type="hidden" name="last_name" value="{{ old('last_name', $last) }}">
                            <input type="hidden" name="email" value="{{ old('email', $email) }}">
                        </div>

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
                                        <option value="{{ $d->id }}"
                                            @selected((string) old('doctor_id', $autoAssignedDoctorId) === (string) $d->id)>
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
                            <input type="hidden" name="doctor_id" id="doctor_id" value="{{ old('doctor_id') }}">
                        @endif

                        <div class="kt-form-row">
                            <div class="kt-form-group">
                                <label class="kt-form-label" for="date">Date</label>
                                <input
                                    id="date"
                                    type="date"
                                    name="date"
                                    class="kt-form-input @error('date') kt-input--error @enderror"
                                    value="{{ old('date') }}"
                                    min="{{ now()->toDateString() }}"
                                    required
                                >
                                @error('date')<span class="kt-form-error">{{ $message }}</span>@enderror
                            </div>

                            @if(!$isWalkIn)
                                <div class="kt-form-group">
                                    <label class="kt-form-label" for="time">Time</label>
                                    <input type="hidden" name="request_walkin" id="request_walkin" value="{{ old('request_walkin', 0) ? 1 : 0 }}">
                                    <select class="kt-form-input @error('time') kt-input--error @enderror" name="time" id="time" required>
                                        <option value="">{{ $doctorRequired ? 'Select doctor and date first' : 'Select date first' }}</option>
                                    </select>
                                    <div id="slotGrid" class="kt-booking-v2__slot-grid"></div>
                                    <span id="timeHelp" class="kt-form-help"></span>
                                    <div id="walkInFallback" class="kt-booking-v2__walkin kt-hidden">
                                        <p class="kt-booking-v2__walkin-text" id="walkInFallbackText">All schedule slots are filled. You can request walk-in for today.</p>
                                        <button type="button" class="kt-booking-v2__walkin-btn" id="walkInRequestBtn" aria-pressed="false">
                                            <span>Request walk-in for today</span>
                                        </button>
                                        <div class="kt-booking-v2__walkin-hint" id="walkInSelectedHint">Walk-in request selected. No slot is reserved until staff approval.</div>
                                    </div>
                                    @error('time')<span class="kt-form-error">{{ $message }}</span>@enderror
                                    @error('request_walkin')<span class="kt-form-error">{{ $message }}</span>@enderror
                                </div>
                            @else
                                <input type="hidden" name="time" value="">
                            @endif
                        </div>

                        @if(!$needsDetails)
                            <input type="hidden" name="contact" value="{{ $contactVal }}">
                            <input type="hidden" name="address" value="{{ $addressVal }}">
                            <input type="hidden" name="birthdate" value="{{ $birthdateVal }}">
                        @else
                            <div class="kt-form-row">
                                <div class="kt-form-group">
                                    <label class="kt-form-label">Contact Number</label>
                                    <input class="kt-form-input @error('contact') kt-input--error @enderror" type="tel" name="contact" value="{{ $contactVal }}" placeholder="09xx xxx xxxx" inputmode="tel" required>
                                    @error('contact')<span class="kt-form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="kt-form-group">
                                    <label class="kt-form-label">Birthdate</label>
                                    <input class="kt-form-input @error('birthdate') kt-input--error @enderror" type="date" name="birthdate" value="{{ $birthdateVal }}" max="{{ now()->subDay()->toDateString() }}" required>
                                    @error('birthdate')<span class="kt-form-error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="kt-form-group">
                                <label class="kt-form-label">Address</label>
                                <input class="kt-form-input @error('address') kt-input--error @enderror" type="text" name="address" value="{{ $addressVal }}" placeholder="Complete address" required>
                                @error('address')<span class="kt-form-error">{{ $message }}</span>@enderror
                            </div>
                        @endif

                        <div class="kt-form-group">
                            <label class="kt-form-label">Message (optional)</label>
                            <textarea class="kt-form-input @error('message') kt-input--error @enderror" name="message" rows="3" maxlength="500" placeholder="Anything you want us to know?">{{ old('message') }}</textarea>
                            @error('message')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>

                        <button class="kt-form-submit" type="submit"><span>Confirm Booking</span></button>
                    </form>
                </article>

                <aside class="kt-booking-v2__aside">
                    <div class="kt-booking-v2__photo">
                        <img src="{{ asset('images/pic2.jpg') }}" alt="" loading="lazy">
                    </div>
                    <article class="kt-booking-v2__aside-card">
                        <h3>Before You Submit</h3>
                        <ul>
                            <li>Choose your preferred dentist and date first.</li>
                            <li>Pending requests are reviewed by clinic staff.</li>
                            <li>If fully booked today, request walk-in approval.</li>
                        </ul>
                    </article>
                    <article class="kt-booking-v2__aside-card">
                        <h3>Clinic Hours</h3>
                        <p>Mon-Sat, 9:00 AM to 5:00 PM</p>
                        <small>Schedules may be adjusted depending on clinic flow.</small>
                    </article>
                    @if($doctorRestrictionEnabled)
                        <article class="kt-booking-v2__aside-card">
                            <h3>Treatment Assignment</h3>
                            <p>
                                This treatment follows a dentist assignment rule set by the clinic.
                                @if($autoAssignedDoctorId && $doctors->firstWhere('id', $autoAssignedDoctorId))
                                    {{ $doctors->firstWhere('id', $autoAssignedDoctorId)->name }} will be auto-selected for your booking.
                                @elseif($doctors->count() > 1)
                                    Only the dentists assigned to this treatment will appear in the list.
                                @else
                                    Online booking will stay unavailable until a dentist is assigned.
                                @endif
                            </p>
                        </article>
                    @endif
                </aside>
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
(function () {
    'use strict';
    var fullNameEl = document.getElementById('full_name');
    var firstEl = document.querySelector('input[name="first_name"]');
    var middleEl = document.querySelector('input[name="middle_name"]');
    var lastEl = document.querySelector('input[name="last_name"]');
    var form = document.getElementById('ktPublicBookingForm');

    function splitName(name) {
        name = (name || '').trim().replace(/\s+/g, ' ');
        if (!name) return { first: '', middle: '', last: '' };
        var parts = name.split(' ');
        return {
            first: parts[0] || '',
            middle: parts.length > 2 ? parts.slice(1, -1).join(' ') : '',
            last: parts.length > 1 ? parts[parts.length - 1] : (parts[0] || '')
        };
    }

    function syncName() {
        if (!fullNameEl || !firstEl || !middleEl || !lastEl) return;
        var n = splitName(fullNameEl.value);
        firstEl.value = n.first;
        middleEl.value = n.middle;
        lastEl.value = n.last;
    }

    if (fullNameEl) {
        fullNameEl.addEventListener('input', syncName);
        fullNameEl.addEventListener('blur', syncName);
        syncName();
    }
    if (form) form.addEventListener('submit', syncName);
})();
</script>
@endpush

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
    var walkInInput = document.getElementById('request_walkin');
    var walkInBox = document.getElementById('walkInFallback');
    var walkInBtn = document.getElementById('walkInRequestBtn');
    var walkInText = document.getElementById('walkInFallbackText');
    var walkInHint = document.getElementById('walkInSelectedHint');

    var doctorRequired = @json((bool) $doctorRequired);
    var oldTime = @json(old('time'));
    var oldWalkInRequested = @json((bool) old('request_walkin'));
    var todayIso = @json(now()->toDateString());
    var seededOldWalkIn = false;
    var seededOldTime = false;
    var seededOldDoctor = false;
    var initialAutoDoctorId = doctorEl ? String(doctorEl.getAttribute('data-auto-doctor-id') || '') : '';
    var treatmentRestricted = doctorEl ? doctorEl.getAttribute('data-doctor-restricted') === '1' : false;

    if (!dateEl || !timeEl) return;

    function fmt12h(t){
        if(!t || typeof t !== 'string' || !t.includes(':')) return t;
        var p = t.split(':');
        var h = parseInt(p[0], 10);
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = (h % 12) || 12;
        return h + ':' + p[1] + ' ' + ampm;
    }

    function setWalkInRequested(enabled){
        if (walkInInput) walkInInput.value = enabled ? '1' : '0';
        timeEl.required = !enabled;
        timeEl.disabled = enabled;
        if (enabled) timeEl.value = '';
        if (walkInBox) walkInBox.classList.toggle('is-selected', enabled);
        if (walkInHint) walkInHint.classList.toggle('is-visible', enabled);
        if (walkInBtn) walkInBtn.classList.toggle('is-active', enabled);
        if (gridEl) gridEl.classList.toggle('is-dimmed', enabled);
        if (helpEl && enabled) helpEl.textContent = 'Walk-in request selected. No slot is reserved until staff approval.';
    }

    function hideWalkInOption(){
        if (!walkInBox) return;
        walkInBox.classList.add('kt-hidden');
        setWalkInRequested(false);
    }

    function showWalkInOption(message){
        if (!walkInBox) return;
        walkInBox.classList.remove('kt-hidden');
        if (walkInText && message) walkInText.textContent = message;
    }

    function setLoading(msg){
        timeEl.disabled = false;
        timeEl.innerHTML = '<option value="">' + msg + '</option>';
        if (helpEl) helpEl.textContent = '';
        if (gridEl) gridEl.innerHTML = '';
        hideWalkInOption();
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

    function populateDoctors(payload){
        if (!doctorEl) return;

        resetDoctorOptions();

        var doctors = Array.isArray(payload && payload.doctors) ? payload.doctors : [];
        var meta = payload && payload.meta ? payload.meta : {};
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

        if (!seededOldDoctor && doctorEl.querySelector('option[value="' + @json((string) old('doctor_id')) + '"]:not([disabled])')) {
            doctorEl.value = @json((string) old('doctor_id'));
        }
        seededOldDoctor = true;

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
        populateDoctors(await res.json());
    }

    function renderGrid(slots){
        if (!gridEl) return;
        gridEl.innerHTML = slots.map(function (t) {
            return '<button type="button" class="kt-booking-v2__slot" data-time="' + t + '">' + fmt12h(t) + '</button>';
        }).join('');
        var btns = Array.from(gridEl.querySelectorAll('.kt-booking-v2__slot'));
        function markActive(val){
            btns.forEach(function(b){ b.classList.toggle('is-active', b.dataset.time === val); });
        }
        markActive(timeEl.value);
        btns.forEach(function(btn){
            btn.addEventListener('click', function(){
                timeEl.value = btn.dataset.time;
                markActive(btn.dataset.time);
                setWalkInRequested(false);
            });
        });
    }

    async function loadSlots(){
        var date = dateEl.value;
        await syncDoctorsByDate(date);
        var doctorId = doctorEl ? doctorEl.value : '';
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

        if (!slots.length){
            timeEl.disabled = true;
            timeEl.innerHTML = '<option value="">No available slots</option>';
            var doctorUnavailable = Boolean(data && data.meta && data.meta.doctor_unavailable);
            var bookingBlocked = Boolean(data && data.meta && data.meta.booking_blocked);
            var reason = (data && data.meta && data.meta.doctor_unavailable_reason) || 'Unavailable on this date.';
            if (helpEl) {
                helpEl.textContent = bookingBlocked
                    ? ((data.meta && data.meta.booking_blocked_reason) || 'No dentist available for this treatment.')
                    : doctorUnavailable
                    ? 'Selected dentist is unavailable: ' + reason
                    : 'No available schedule slots for this date.';
            }
            if (gridEl) gridEl.innerHTML = '';
            if (doctorUnavailable) {
                hideWalkInOption();
                return;
            }
            var isTodaySelected = (date === todayIso);
            if (isTodaySelected) {
                showWalkInOption('All schedule slots are filled. You can submit a walk-in request for today.');
                if (!seededOldWalkIn && oldWalkInRequested) {
                    setWalkInRequested(true);
                    seededOldWalkIn = true;
                }
            } else {
                hideWalkInOption();
            }
            return;
        }

        hideWalkInOption();
        timeEl.disabled = false;
        timeEl.innerHTML = '<option value="">Select time...</option>' + slots.map(function(t){
            var selected = (!seededOldTime && oldTime && oldTime === t) ? 'selected' : '';
            return '<option value="' + t + '" ' + selected + '>' + fmt12h(t) + '</option>';
        }).join('');

        if (!seededOldTime && oldTime) {
            timeEl.value = oldTime;
            seededOldTime = true;
        }
        renderGrid(slots);
        if (helpEl) {
            var suffix = (doctorRequired && doctorId) ? ' for this dentist.' : '.';
            helpEl.textContent = slots.length + ' slot(s) available' + suffix;
        }
    }

    if (walkInBtn) {
        walkInBtn.addEventListener('click', function(){
            if (walkInBox && walkInBox.classList.contains('kt-hidden')) return;
            var next = !(walkInInput && walkInInput.value === '1');
            setWalkInRequested(next);
        });
    }

    timeEl.addEventListener('change', function(){ if (timeEl.value) setWalkInRequested(false); });
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
