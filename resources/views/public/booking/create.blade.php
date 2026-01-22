{{-- resources/views/public/booking/create.blade.php --}}
@extends('layouts.public')
@section('title', 'Book — ' . $service->name)

@section('content')
@php
    $u = auth()->user();

    // ✅ pull most recently used booking name (if user already booked before)
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
                if ($rebuilt !== '') $recentBookingName = $rebuilt;
            }
        }
    }

    // ✅ editable full name
    $fullName = trim(old('full_name', $recentBookingName ?? ($u->name ?? '')));
    $email    = trim(old('email', $u->email ?? ''));

    // ✅ IMPORTANT: use controller-provided flag if available
    $isWalkIn = $isWalkIn ?? (function() use ($service){
        $durRaw = $service->duration_minutes ?? null;
        if ($durRaw === null || $durRaw === '') return true;
        if (is_numeric($durRaw)) {
            $d = (int)$durRaw;
            return $d > 0 && $d <= 5;
        }
        return false;
    })();

    // ✅ Best-effort split based on CURRENT fullName
    $parts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);
    $first = $parts[0] ?? '';
    $last  = count($parts) > 1 ? $parts[count($parts)-1] : ($parts[0] ?? '');
    $middle = (count($parts) > 2) ? implode(' ', array_slice($parts, 1, -1)) : '';

    if ($first === '' && $fullName !== '') $first = $fullName;
    if ($last === '' && $first !== '') $last = $first;

    $doctorRequired = ($doctors->count() > 0);
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

<section class="section section-soft kt-booking-page {{ $hasSuccess ? 'kt-booking-success' : 'kt-booking-form' }}">
    <style>
        .kt-booking-page{ padding-bottom: 24px; }

        /* ✅ Only the FORM view needs extra bottom padding on mobile (sticky submit) */
        @media (max-width: 768px){
            .kt-booking-form{ padding-bottom: 130px !important; }
            .kt-booking-success{ padding-bottom: 24px !important; }
        }

        .kt-booking-card{ height: 100%; }
        .kt-field-help{ min-height: 18px; }

        .kt-slot-grid{
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        @media (min-width: 576px){
            .kt-slot-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .kt-slot{
            border: 1px solid var(--kt-border, rgba(15,23,42,.12));
            background: rgba(255,255,255,.88);
            border-radius: 16px;
            padding: 10px 10px;
            text-align: left;
            font-weight: 900;
            font-size: 13px;
            line-height: 1.15;
            cursor: pointer;
            transition: transform 120ms ease, opacity 120ms ease, background 120ms ease;
            width: 100%;
        }
        html[data-theme="dark"] .kt-slot{ background: rgba(17,24,39,.55); }
        .kt-slot:active{ transform: scale(.99); }
        .kt-slot.is-active{
            border-color: rgba(194,138,99,.55);
            background: rgba(194,138,99,.12);
        }

        .kt-sticky-submit{
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom, 0px));
            background: linear-gradient(to top, rgba(255,255,255,.92), rgba(255,255,255,.55), rgba(255,255,255,0));
            backdrop-filter: blur(10px);
            z-index: 5000;
        }
        html[data-theme="dark"] .kt-sticky-submit{
            background: linear-gradient(to top, rgba(17,24,39,.92), rgba(17,24,39,.55), rgba(17,24,39,0));
        }

        .kt-help{
            font-size: 12px;
            color: rgba(15, 23, 42, .6);
            font-weight: 650;
        }
        html[data-theme="dark"] .kt-help{ color: rgba(226, 232, 240, .65); }

        .kt-side-img{ height: 520px; }
        @media (max-width: 991.98px){
            .kt-side-img{ height: 320px; }
        }
        .kt-side-img img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
        }

        .kt-step-pill{
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--kt-border, rgba(15,23,42,.12));
            background: rgba(255,255,255,.75);
            border-radius: 999px;
            padding: 6px 10px;
            font-weight: 850;
            font-size: 12px;
        }
        html[data-theme="dark"] .kt-step-pill{ background: rgba(17,24,39,.55); }

        /* ===========================
           ✅ SUCCESS SUMMARY (MOBILE FIX)
           =========================== */
        .kt-success-wrap .sec-title{
            font-size: clamp(24px, 5.6vw, 32px);
            line-height: 1.12;
        }

        .kt-success-summary{
            border: 1px solid var(--kt-border, rgba(15,23,42,.12));
            background: rgba(255,255,255,.78);
            border-radius: 18px;
            padding: 14px;
        }
        html[data-theme="dark"] .kt-success-summary{
            background: rgba(17,24,39,.45);
        }

        .kt-summary-grid{
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 10px;
        }
        @media (min-width: 576px){
            .kt-summary-grid{ grid-template-columns: 1fr 1fr; }
        }

        .kt-summary-item{
            border: 1px solid rgba(15,23,42,.10);
            background: rgba(255,255,255,.65);
            border-radius: 16px;
            padding: 12px 12px;
        }
        html[data-theme="dark"] .kt-summary-item{
            border-color: rgba(226,232,240,.10);
            background: rgba(2,6,23,.25);
        }
        .kt-summary-label{
            font-size: 12px;
            font-weight: 800;
            color: rgba(15,23,42,.55);
        }
        html[data-theme="dark"] .kt-summary-label{ color: rgba(226,232,240,.65); }
        .kt-summary-value{
            font-weight: 950;
            margin-top: 2px;
            line-height: 1.2;
            word-break: break-word;
        }

        .kt-success-actions{
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 14px;
        }
        @media (min-width: 576px){
            .kt-success-actions{ grid-template-columns: 1fr 1fr; }
        }
    </style>

    <div class="container">
        <x-back-button
            fallback="{{ route('public.services.show', $service->id) }}"
            class="text-decoration-none fw-bold"
            icon_class="fa-solid fa-arrow-left me-1"
            label="Back"
        />

        <div class="row g-4 mt-2 align-items-stretch">
            <div class="col-lg-6 d-flex">
                @if($hasSuccess)
                    <div class="card card-soft p-4 kt-booking-card w-100 kt-success-wrap">
                        <h2 class="sec-title mb-2">Booking submitted</h2>
                        <div class="sec-sub">
                            Saved as <b>Pending</b>. Staff will confirm it and email you.
                        </div>

                        <div class="kt-success-summary mt-3">
                            <div class="kt-summary-grid">
                                <div class="kt-summary-item">
                                    <div class="kt-summary-label">Service</div>
                                    <div class="kt-summary-value">{{ $successAppointment->service->name ?? '—' }}</div>
                                </div>

                                <div class="kt-summary-item">
                                    <div class="kt-summary-label">Doctor</div>
                                    <div class="kt-summary-value">{{ $successAppointment->doctor->name ?? ($successAppointment->dentist_name ?? '—') }}</div>
                                </div>

                                <div class="kt-summary-item">
                                    <div class="kt-summary-label">Date</div>
                                    <div class="kt-summary-value">{{ $successAppointment->appointment_date ?? '—' }}</div>
                                </div>

                                <div class="kt-summary-item">
                                    <div class="kt-summary-label">Time</div>
                                    <div class="kt-summary-value">
                                        {{ !empty($successAppointment->appointment_time) ? $successAppointment->appointment_time : 'WALK-IN' }}
                                    </div>
                                </div>
                            </div>

                            <div class="kt-success-actions">
                                <a class="btn kt-btn kt-btn-primary text-white" href="{{ route('public.services.index') }}">
                                    <i class="fa-solid fa-calendar-plus me-1"></i> Book another service
                                </a>
                                <a class="btn kt-btn kt-btn-outline" href="{{ route('profile.show') }}">
                                    <i class="fa-solid fa-user me-1"></i> My profile
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card card-soft p-4 kt-booking-card w-100">
                        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                            <div>
                                <h2 class="sec-title mb-1">Book: {{ $service->name }}</h2>
                                <div class="sec-sub mb-0">
                                    @if($isWalkIn)
                                        Choose doctor (if required) and a date. This is a <b>Walk-in</b> service — come anytime during clinic hours.
                                    @else
                                        @if($needsDetails)
                                            Select doctor, date, time — then fill <b>contact</b>, <b>address</b>, and <b>birthdate</b>.
                                        @else
                                            Select doctor, date, and time. We’ll use your saved contact, address, and birthdate.
                                        @endif
                                    @endif
                                </div>
                            </div>

                            @if($isWalkIn)
                                <span class="kt-step-pill">
                                    <i class="fa-solid fa-person-walking"></i> Walk-in
                                </span>
                            @else
                                <span class="kt-step-pill">
                                    <i class="fa-regular fa-clock"></i> Scheduled
                                </span>
                            @endif
                        </div>

                        @if($isWalkIn)
                            <div class="alert alert-light border mt-3" style="border-radius:16px;">
                                <div style="font-weight:900;">Walk-in service</div>
                                <div class="small text-muted">
                                    Come anytime during clinic hours (Mon–Sat 9:00 AM – 5:00 PM). No time slot needed.
                                </div>
                            </div>
                        @else
                            @if(!$needsDetails)
                                <div class="alert alert-light border mt-3" style="border-radius:16px;">
                                    <div style="font-weight:900;">Using saved details</div>
                                    <div class="small text-muted">
                                        Contact, address, and birthdate are already on record — no need to re-enter.
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger mt-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form class="mt-3" method="POST" action="{{ route('public.booking.store', $service->id) }}">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold">Full Name</label>
                                    <input
                                        class="form-control"
                                        id="full_name"
                                        name="full_name"
                                        value="{{ $fullName }}"
                                        placeholder="Type your real full name"
                                        required
                                    >
                                    <div class="kt-help mt-1 kt-field-help">
                                        We remembered the name you used last time. Edit it if needed.
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Email</label>
                                    <input class="form-control" value="{{ $email }}" readonly aria-readonly="true">
                                    <div class="kt-field-help"></div>
                                </div>
                            </div>

                            <input type="hidden" name="first_name" value="{{ old('first_name', $first) }}">
                            <input type="hidden" name="middle_name" value="{{ old('middle_name', $middle) }}">
                            <input type="hidden" name="last_name" value="{{ old('last_name', $last) }}">
                            <input type="hidden" name="email" value="{{ old('email', $email) }}">

                            <hr class="my-4">

                            @if($doctorRequired)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Step 1: Select Doctor</label>
                                    <select class="form-select" name="doctor_id" id="doctor_id" required>
                                        <option value="">-- Choose Doctor --</option>
                                        @foreach($doctors as $d)
                                            <option value="{{ $d->id }}" @selected(old('doctor_id') == $d->id)>
                                                {{ $d->name ?? ('Doctor #' . $d->id) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            @else
                                <input type="hidden" name="doctor_id" id="doctor_id" value="{{ old('doctor_id') }}">
                            @endif

                            <div class="row g-3">
                                <div class="{{ $isWalkIn ? 'col-md-6' : 'col-md-4' }}">
                                    <label class="form-label fw-bold">Step 2: Date</label>
                                    <input type="date"
                                           class="form-control"
                                           name="date"
                                           id="date"
                                           value="{{ old('date') }}"
                                           min="{{ now()->toDateString() }}"
                                           required>
                                    @error('date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                @if(!$isWalkIn)
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Step 3: Time</label>
                                        <select class="form-select" name="time" id="time" required>
                                            <option value="">
                                                {{ $doctorRequired ? 'Select doctor + date first…' : 'Select date first…' }}
                                            </option>
                                        </select>

                                        <div class="d-md-none">
                                            <div class="small text-muted mt-2" style="font-weight:700;">Tap a time slot:</div>
                                            <div id="slotGrid" class="kt-slot-grid"></div>
                                        </div>

                                        <div class="small text-muted mt-1" id="timeHelp"></div>
                                        @error('time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                @else
                                    <input type="hidden" name="time" value="">
                                @endif

                                @if($needsDetails)
                                    <div class="{{ $isWalkIn ? 'col-md-6' : 'col-md-4' }}">
                                        <label class="form-label fw-bold">{{ $isWalkIn ? 'Step 3' : 'Step 4' }}: Contact Number</label>
                                        <input class="form-control"
                                               type="tel"
                                               name="contact"
                                               value="{{ $contactVal }}"
                                               placeholder="09xx xxx xxxx"
                                               inputmode="tel"
                                               required>
                                        <div class="kt-help mt-1 kt-field-help">We’ll use this to confirm your booking.</div>
                                        @error('contact')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                @endif
                            </div>

                            @if(!$needsDetails)
                                <input type="hidden" name="contact" value="{{ $contactVal }}">
                                <input type="hidden" name="address" value="{{ $addressVal }}">
                                <input type="hidden" name="birthdate" value="{{ $birthdateVal }}">
                            @else
                                <div class="row g-3 mt-1">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">{{ $isWalkIn ? 'Step 4' : 'Step 5' }}: Address</label>
                                        <input class="form-control"
                                               type="text"
                                               name="address"
                                               value="{{ $addressVal }}"
                                               placeholder="Complete address"
                                               required>
                                        @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">{{ $isWalkIn ? 'Step 5' : 'Step 6' }}: Birthdate</label>
                                        <input class="form-control"
                                               type="date"
                                               name="birthdate"
                                               value="{{ $birthdateVal }}"
                                               max="{{ now()->subDay()->toDateString() }}"
                                               required>
                                        <div class="kt-help mt-1 kt-field-help">Used for your patient record.</div>
                                        @error('birthdate')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @endif

                            <div class="mt-3">
                                <label class="form-label fw-bold">Message (optional)</label>
                                <textarea class="form-control" name="message" rows="3" maxlength="500"
                                          placeholder="Anything you want us to know?">{{ old('message') }}</textarea>
                                @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <button class="btn kt-btn kt-btn-primary text-white mt-4 d-none d-md-inline-flex" type="submit">
                                <i class="fa-solid fa-circle-check me-1"></i> Confirm Booking
                            </button>

                            <div class="kt-sticky-submit d-md-none">
                                <div class="container">
                                    <button class="btn kt-btn kt-btn-primary text-white w-100" type="submit">
                                        <i class="fa-solid fa-circle-check me-1"></i> Confirm Booking
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <div class="col-lg-6 d-flex">
                <div class="img-tile kt-side-img w-100">
                    <img src="{{ asset('assets/img/public/pic7.jpg') }}" alt="Clinic">
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// ✅ Keep first/middle/last hidden fields synced with editable full_name
(function(){
    const fullNameEl = document.getElementById('full_name');
    const firstEl  = document.querySelector('input[name="first_name"]');
    const middleEl = document.querySelector('input[name="middle_name"]');
    const lastEl   = document.querySelector('input[name="last_name"]');
    const form = fullNameEl ? fullNameEl.closest('form') : null;

    function splitName(n){
        n = (n || '').trim().replace(/\s+/g, ' ');
        if(!n) return { first:'', middle:'', last:'' };

        const parts = n.split(' ');
        const first = parts[0] || '';
        const last  = parts.length > 1 ? parts[parts.length - 1] : (parts[0] || '');
        const middle = parts.length > 2 ? parts.slice(1, -1).join(' ') : '';
        return { first, middle, last };
    }

    function sync(){
        if(!fullNameEl || !firstEl || !middleEl || !lastEl) return;
        const s = splitName(fullNameEl.value);
        firstEl.value = s.first;
        middleEl.value = s.middle;
        lastEl.value = s.last;
    }

    if(fullNameEl){
        fullNameEl.addEventListener('input', sync);
        fullNameEl.addEventListener('blur', sync);
        sync();
    }
    if(form){
        form.addEventListener('submit', sync);
    }
})();
</script>
@endpush

{{-- ✅ Slots script ONLY for scheduled services --}}
@if(!$isWalkIn)
@push('scripts')
<script>
(function(){
    const serviceId = @json($service->id);
    const dateEl = document.getElementById('date');
    const timeEl = document.getElementById('time');
    const helpEl = document.getElementById('timeHelp');
    const doctorEl = document.getElementById('doctor_id');
    const gridEl = document.getElementById('slotGrid');

    const doctorRequired = @json($doctors->count() > 0);
    const oldTime = @json(old('time'));

    if (!dateEl || !timeEl) return;

    function setLoading(msg){
        timeEl.innerHTML = `<option value="">${msg}</option>`;
        if (helpEl) helpEl.textContent = '';
        if (gridEl) gridEl.innerHTML = '';
    }

    function fmt12h(t){
        if(!t || typeof t !== 'string' || !t.includes(':')) return t;
        const [hh, mm] = t.split(':');
        let h = parseInt(hh, 10);
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = (h % 12) || 12;
        return `${h}:${mm} ${ampm}`;
    }

    function renderGrid(slots){
        if (!gridEl) return;

        gridEl.innerHTML = slots.map(t => `
          <button type="button" class="kt-slot" data-time="${t}">
            ${fmt12h(t)}
          </button>
        `).join('');

        const btns = Array.from(gridEl.querySelectorAll('.kt-slot'));
        const markActive = (val) => btns.forEach(b => b.classList.toggle('is-active', b.dataset.time === val));

        markActive(timeEl.value);

        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                timeEl.value = btn.dataset.time;
                markActive(btn.dataset.time);
            });
        });
    }

    async function loadSlots(){
        const date = dateEl.value;
        const doctorId = doctorEl?.value || '';

        if (doctorRequired && doctorEl && !doctorId){
            setLoading('Select doctor first…');
            return;
        }
        if(!date){
            setLoading('Select date first…');
            return;
        }

        setLoading('Loading available times…');

        const url = new URL(`/book/${serviceId}/slots`, window.location.origin);
        url.searchParams.set('date', date);
        if (doctorRequired) url.searchParams.set('doctor_id', doctorId);

        let res;
        try {
            res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
        } catch (e) {
            setLoading('Unable to load slots');
            if (helpEl) helpEl.textContent = '';
            return;
        }

        if(!res.ok){
            setLoading('Unable to load slots');
            if (helpEl) helpEl.textContent = '';
            return;
        }

        const data = await res.json();
        const slots = data.slots || [];

        if(!slots.length){
            timeEl.innerHTML = `<option value="">No available slots</option>`;
            if (helpEl) helpEl.textContent = 'No slots available.';
            if (gridEl) gridEl.innerHTML = '';
            return;
        }

        timeEl.innerHTML = `<option value="">Select time…</option>` + slots.map(t => {
            const selected = (oldTime && oldTime === t) ? 'selected' : '';
            return `<option value="${t}" ${selected}>${fmt12h(t)}</option>`;
        }).join('');

        if (oldTime) timeEl.value = oldTime;

        renderGrid(slots);

        if (helpEl){
            const suffix = (doctorRequired && doctorId) ? ' for this dentist.' : '.';
            helpEl.textContent = `${slots.length} slot(s) available${suffix}`;
        }
    }

    dateEl.addEventListener('change', loadSlots);
    if (doctorEl) doctorEl.addEventListener('change', loadSlots);

    if(dateEl.value && (!doctorRequired || (doctorEl && doctorEl.value))) loadSlots();
})();
</script>
@endpush
@endif

@endsection
