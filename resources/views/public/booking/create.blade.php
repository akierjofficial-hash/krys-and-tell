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
        .kt-layout-main{ min-height: 100%; }
        .kt-layout-aside{
            position: sticky;
            top: 106px;
        }
        .kt-booking-card{
            height: 100%;
            border-radius: 22px;
            border: 1px solid rgba(15,23,42,.10);
            background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.86));
            box-shadow: 0 10px 30px rgba(15,23,42,.08);
        }
        html[data-theme="dark"] .kt-booking-card{
            border-color: rgba(226,232,240,.12);
            background: linear-gradient(180deg, rgba(15,23,42,.74), rgba(2,6,23,.62));
            box-shadow: 0 10px 28px rgba(2,6,23,.36);
        }

        .kt-book-head{
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .kt-book-kicker{
            margin: 0 0 6px;
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            font-weight: 900;
            color: rgba(15,23,42,.55);
        }
        html[data-theme="dark"] .kt-book-kicker{ color: rgba(226,232,240,.62); }
        .kt-mode-pill{
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            border: 1px solid var(--kt-border, rgba(15,23,42,.12));
            background: rgba(255,255,255,.75);
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 850;
            white-space: nowrap;
        }
        html[data-theme="dark"] .kt-mode-pill{ background: rgba(17,24,39,.6); }
        .kt-mode-pill i{ font-size: 12px; }

        .kt-inline-note{
            margin-top: 14px;
            border-radius: 16px;
            border: 1px solid rgba(15,23,42,.10);
            background: rgba(255,255,255,.78);
            padding: 12px 14px;
        }
        html[data-theme="dark"] .kt-inline-note{
            border-color: rgba(226,232,240,.12);
            background: rgba(2,6,23,.28);
        }
        .kt-inline-note-title{
            font-weight: 900;
            font-size: 13px;
            margin-bottom: 4px;
        }
        .kt-inline-note-desc{
            font-size: 12px;
            color: rgba(15,23,42,.62);
        }
        html[data-theme="dark"] .kt-inline-note-desc{ color: rgba(226,232,240,.72); }

        .kt-form-shell{ margin-top: 16px; }
        .kt-label{
            display: inline-block;
            margin-bottom: 6px;
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            font-weight: 900;
            color: rgba(15,23,42,.62);
        }
        html[data-theme="dark"] .kt-label{ color: rgba(226,232,240,.72); }
        .kt-help{
            font-size: 12px;
            color: rgba(15,23,42,.6);
            font-weight: 650;
        }
        html[data-theme="dark"] .kt-help{ color: rgba(226,232,240,.65); }
        .kt-field-help{ min-height: 18px; }
        .kt-booking-card .form-control,
        .kt-booking-card .form-select{
            border-radius: 14px;
            border-color: rgba(15,23,42,.16);
            background: rgba(255,255,255,.96);
            min-height: 46px;
            padding: 10px 12px;
        }
        html[data-theme="dark"] .kt-booking-card .form-control,
        html[data-theme="dark"] .kt-booking-card .form-select{
            border-color: rgba(226,232,240,.16);
            background: rgba(15,23,42,.66);
            color: #e5e7eb;
        }

        .kt-time-panel{
            margin-top: 8px;
            border-radius: 16px;
            border: 1px solid rgba(15,23,42,.10);
            background: rgba(255,255,255,.74);
            padding: 12px;
        }
        html[data-theme="dark"] .kt-time-panel{
            border-color: rgba(226,232,240,.12);
            background: rgba(2,6,23,.24);
        }
        .kt-slot-heading{
            margin-top: 10px;
            font-size: 12px;
            font-weight: 800;
            color: rgba(15,23,42,.58);
        }
        html[data-theme="dark"] .kt-slot-heading{ color: rgba(226,232,240,.68); }
        .kt-slot-grid{
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(84px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }
        .kt-slot-grid.is-dimmed{
            opacity: .45;
            pointer-events: none;
        }
        .kt-slot{
            border: 1px solid rgba(15,23,42,.12);
            background: rgba(255,255,255,.88);
            border-radius: 12px;
            padding: 10px 8px;
            text-align: center;
            font-weight: 850;
            font-size: 13px;
            line-height: 1.1;
            white-space: nowrap;
            min-height: 44px;
            cursor: pointer;
            transition: transform 120ms ease, border-color 120ms ease, background 120ms ease;
            width: 100%;
        }
        html[data-theme="dark"] .kt-slot{
            border-color: rgba(226,232,240,.18);
            background: rgba(17,24,39,.55);
        }
        .kt-slot:active{ transform: scale(.99); }
        .kt-slot.is-active{
            border-color: rgba(194,138,99,.68);
            background: rgba(194,138,99,.14);
        }

        .kt-walkin-fallback{
            margin-top: 10px;
            padding: 12px;
            border-radius: 14px;
            border: 1px dashed rgba(194,138,99,.5);
            background: rgba(194,138,99,.10);
            transition: border-color 120ms ease, background 120ms ease;
        }
        .kt-walkin-fallback.is-selected{
            border-style: solid;
            border-color: rgba(194,138,99,.75);
            background: rgba(194,138,99,.18);
        }
        .kt-walkin-row{
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .kt-walkin-icon{
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(194,138,99,.22);
            color: #8d5d34;
            flex: 0 0 auto;
        }
        .kt-walkin-fallback .title{
            font-weight: 900;
            font-size: 13px;
            line-height: 1.2;
            color: var(--kt-text, #0f172a);
        }
        .kt-walkin-fallback .desc{
            font-size: 12px;
            color: rgba(15,23,42,.62);
            margin-top: 2px;
        }
        html[data-theme="dark"] .kt-walkin-fallback .desc{ color: rgba(226,232,240,.72); }
        .kt-walkin-btn{
            margin-top: 10px;
            border: 1px solid rgba(194,138,99,.55);
            background: rgba(255,255,255,.78);
            color: var(--kt-text, #0f172a);
            border-radius: 12px;
            padding: 9px 12px;
            font-weight: 850;
            font-size: 12px;
            cursor: pointer;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .kt-walkin-btn.is-active{
            background: rgba(194,138,99,.28);
            border-color: rgba(194,138,99,.82);
        }
        .kt-walkin-state{
            display: none;
            margin-top: 8px;
            font-size: 12px;
            font-weight: 700;
            color: rgba(15,23,42,.66);
        }
        .kt-walkin-state.is-visible{ display: block; }
        html[data-theme="dark"] .kt-walkin-state{ color: rgba(226,232,240,.74); }

        .kt-sticky-submit{
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 8px 12px calc(10px + env(safe-area-inset-bottom, 0px));
            background: linear-gradient(to top, rgba(255,255,255,.94), rgba(255,255,255,.50), rgba(255,255,255,0));
            backdrop-filter: blur(10px);
            z-index: 5000;
        }
        .kt-sticky-card{
            border-radius: 16px;
            border: 1px solid rgba(15,23,42,.1);
            background: rgba(255,255,255,.88);
            padding: 8px;
            box-shadow: 0 10px 26px rgba(15,23,42,.12);
        }
        .kt-sticky-note{
            font-size: 11px;
            font-weight: 800;
            color: rgba(15,23,42,.55);
            padding: 0 4px 6px;
            letter-spacing: .02em;
        }
        html[data-theme="dark"] .kt-sticky-submit{
            background: linear-gradient(to top, rgba(17,24,39,.95), rgba(17,24,39,.55), rgba(17,24,39,0));
        }
        html[data-theme="dark"] .kt-sticky-card{
            border-color: rgba(226,232,240,.14);
            background: rgba(15,23,42,.88);
        }
        html[data-theme="dark"] .kt-sticky-note{ color: rgba(226,232,240,.68); }

        .kt-side-stack{
            display: grid;
            gap: 14px;
        }
        .kt-side-panel{
            border-radius: 18px;
            border: 1px solid rgba(15,23,42,.10);
            background: rgba(255,255,255,.84);
            box-shadow: 0 14px 34px rgba(15,23,42,.08);
            padding: 16px;
        }
        .kt-side-title{
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 900;
            color: rgba(15,23,42,.56);
            margin-bottom: 8px;
        }
        .kt-side-list{
            margin: 0;
            padding-left: 18px;
            color: rgba(15,23,42,.72);
            font-weight: 650;
            line-height: 1.55;
            display: grid;
            gap: 6px;
        }
        .kt-side-img{ height: 300px; }
        .kt-side-img img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
        }

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

        @media (max-width: 1199.98px){
            .kt-layout-aside{
                position: static;
            }
            .kt-side-stack{
                display: none;
            }
        }
        @media (max-width: 768px){
            .kt-booking-form{ padding-bottom: 140px !important; }
            .kt-booking-success{ padding-bottom: 24px !important; }
            .kt-booking-card{ border-radius: 18px; }
            .kt-booking-card.p-4{ padding: 18px !important; }
            .kt-slot-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>

    <div class="container">
        <x-back-button
            fallback="{{ route('public.services.show', $service->id) }}"
            class="text-decoration-none fw-bold"
            icon_class="fa-solid fa-arrow-left me-1"
            label="Back"
        />

        <div class="row g-4 mt-2 align-items-start">
            <div class="col-12 col-xl-7 d-flex">
                @if($hasSuccess)
                    @php
                        $canEditPending = strtolower((string)($successAppointment->status ?? '')) === 'pending';
                    @endphp
                    <div class="card card-soft p-4 kt-booking-card w-100 kt-success-wrap">
                        <h2 class="sec-title mb-2">{{ session('booking_updated') ? 'Booking updated' : 'Booking submitted' }}</h2>
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
                                        @if(!empty($successAppointment->appointment_time))
                                            {{ $successAppointment->appointment_time }}
                                        @elseif(!empty($successAppointment->is_walk_in_request))
                                            WALK-IN REQUEST
                                        @else
                                            WALK-IN
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="kt-success-actions">
                                @if($canEditPending)
                                    <a class="btn kt-btn kt-btn-outline" href="{{ route('public.booking.edit', $successAppointment->id) }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Not sure? Edit your booking
                                    </a>
                                @endif
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
                        <div class="kt-book-head">
                            <div>
                                <p class="kt-book-kicker">Appointment Request</p>
                                <h2 class="sec-title mb-1">{{ $service->name }}</h2>
                                <div class="sec-sub mb-0">
                                    @if($isWalkIn)
                                        Choose dentist (if required) and date. This is a walk-in service, so no time slot is needed.
                                    @else
                                        @if($needsDetails)
                                            Select dentist, date, and time, then fill contact, address, and birthdate.
                                        @else
                                            Select dentist, date, and time. We will use your saved contact, address, and birthdate.
                                        @endif
                                    @endif
                                </div>
                            </div>

                            @if($isWalkIn)
                                <span class="kt-mode-pill">
                                    <i class="fa-solid fa-person-walking"></i> Walk-in
                                </span>
                            @else
                                <span class="kt-mode-pill">
                                    <i class="fa-regular fa-clock"></i> Scheduled
                                </span>
                            @endif
                        </div>

                        @if($isWalkIn)
                            <div class="kt-inline-note">
                                <div class="kt-inline-note-title">Walk-in service</div>
                                <div class="kt-inline-note-desc">
                                    Visit during clinic hours (Mon-Sat, 9:00 AM to 5:00 PM). Staff will assist based on queue flow.
                                </div>
                            </div>
                        @else
                            @if(!$needsDetails)
                                <div class="kt-inline-note">
                                    <div class="kt-inline-note-title">Using saved details</div>
                                    <div class="kt-inline-note-desc">
                                        Your contact, address, and birthdate are already on file.
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

                        <form class="mt-3 kt-form-shell" method="POST" action="{{ route('public.booking.store', $service->id) }}">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="kt-label">Full Name</label>
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
                                    <label class="kt-label">Email</label>
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
                                    <label class="kt-label">Select Dentist</label>
                                    <select class="form-select" name="doctor_id" id="doctor_id" required>
                                        <option value="">Choose dentist...</option>
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
                                <div class="{{ $isWalkIn ? 'col-md-6' : 'col-md-4 col-lg-4' }}">
                                    <label class="kt-label">Preferred Date</label>
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
                                    <div class="col-md-8 col-lg-8">
                                        <label class="kt-label">Preferred Time</label>
                                        <input type="hidden" name="request_walkin" id="request_walkin" value="{{ old('request_walkin', 0) ? 1 : 0 }}">
                                        <div class="kt-time-panel">
                                            <select class="form-select" name="time" id="time" required>
                                                <option value="">
                                                    {{ $doctorRequired ? 'Select dentist and date first...' : 'Select date first...' }}
                                                </option>
                                            </select>

                                            <div class="kt-slot-heading">Available Slots</div>
                                            <div id="slotGrid" class="kt-slot-grid"></div>

                                            <div class="small text-muted mt-2" id="timeHelp"></div>
                                            <div id="walkInFallback" class="kt-walkin-fallback d-none" aria-live="polite">
                                                <div class="kt-walkin-row">
                                                    <span class="kt-walkin-icon"><i class="fa-solid fa-person-walking"></i></span>
                                                    <div>
                                                        <div class="title">Fully booked today</div>
                                                        <div class="desc" id="walkInFallbackText">
                                                            You can still send a walk-in request for today. Staff may approve based on clinic flow.
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="kt-walkin-btn" id="walkInRequestBtn" aria-pressed="false">
                                                    <i class="fa-regular fa-circle"></i>
                                                    <span>Request walk-in for today</span>
                                                </button>
                                                <div class="kt-walkin-state" id="walkInSelectedHint">
                                                    Walk-in request selected. No time slot will be reserved until staff approval.
                                                </div>
                                            </div>
                                        </div>
                                        @error('time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                        @error('request_walkin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                @else
                                    <input type="hidden" name="time" value="">
                                @endif

                                @if($needsDetails)
                                    <div class="{{ $isWalkIn ? 'col-md-6' : 'col-12 col-md-6' }}">
                                        <label class="kt-label">Contact Number</label>
                                        <input class="form-control"
                                               type="tel"
                                               name="contact"
                                               value="{{ $contactVal }}"
                                               placeholder="09xx xxx xxxx"
                                               inputmode="tel"
                                               required>
                                        <div class="kt-help mt-1 kt-field-help">We will use this to confirm your booking.</div>
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
                                        <label class="kt-label">Address</label>
                                        <input class="form-control"
                                               type="text"
                                               name="address"
                                               value="{{ $addressVal }}"
                                               placeholder="Complete address"
                                               required>
                                        @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="kt-label">Birthdate</label>
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
                                <label class="kt-label">Message (optional)</label>
                                <textarea class="form-control" name="message" rows="3" maxlength="500"
                                          placeholder="Anything you want us to know?">{{ old('message') }}</textarea>
                                @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <button class="btn kt-btn kt-btn-primary text-white mt-4 d-none d-md-inline-flex" type="submit">
                                <i class="fa-solid fa-circle-check me-1"></i> Confirm Booking
                            </button>

                            <div class="kt-sticky-submit d-md-none">
                                <div class="container">
                                    <div class="kt-sticky-card">
                                        <div class="kt-sticky-note">Review details before submitting</div>
                                        <button class="btn kt-btn kt-btn-primary text-white w-100" type="submit">
                                            <i class="fa-solid fa-circle-check me-1"></i> Confirm Booking
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <div class="col-12 col-xl-5">
                <div class="kt-layout-aside">
                    <div class="kt-side-stack">
                        <div class="img-tile kt-side-img w-100">
                            <img src="{{ asset('assets/img/public/pic7.jpg') }}" alt="Clinic">
                        </div>

                        <div class="kt-side-panel">
                            <div class="kt-side-title">Before You Submit</div>
                            <ul class="kt-side-list">
                                <li>Choose your preferred dentist and date first.</li>
                                <li>Only approved requests reserve a final clinic slot.</li>
                                <li>If fully booked today, you can request walk-in approval.</li>
                            </ul>
                        </div>

                        <div class="kt-side-panel">
                            <div class="kt-side-title">Clinic Hours</div>
                            <div style="font-weight:800; color:rgba(15,23,42,.86);">Mon-Sat, 9:00 AM to 5:00 PM</div>
                            <div class="kt-help mt-2">Staff may adjust schedule based on final clinic flow.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
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
    const walkInInput = document.getElementById('request_walkin');
    const walkInBox = document.getElementById('walkInFallback');
    const walkInBtn = document.getElementById('walkInRequestBtn');
    const walkInText = document.getElementById('walkInFallbackText');
    const walkInHint = document.getElementById('walkInSelectedHint');

    const doctorRequired = @json($doctors->count() > 0);
    const oldTime = @json(old('time'));
    const oldWalkInRequested = @json((bool) old('request_walkin'));
    const todayIso = @json(now()->toDateString());

    let seededOldWalkIn = false;
    let seededOldTime = false;

    if (!dateEl || !timeEl) return;

    function setWalkInRequested(enabled){
        if (walkInInput) walkInInput.value = enabled ? '1' : '0';

        timeEl.required = !enabled;
        timeEl.disabled = enabled;

        if (enabled){
            timeEl.value = '';
            if (gridEl) {
                gridEl.querySelectorAll('.kt-slot.is-active').forEach((el) => el.classList.remove('is-active'));
            }
        }

        if (walkInBox) walkInBox.classList.toggle('is-selected', enabled);
        if (gridEl) gridEl.classList.toggle('is-dimmed', enabled);
        if (walkInHint) walkInHint.classList.toggle('is-visible', enabled);

        if (walkInBtn){
            walkInBtn.classList.toggle('is-active', enabled);
            walkInBtn.setAttribute('aria-pressed', enabled ? 'true' : 'false');

            const icon = walkInBtn.querySelector('i');
            const label = walkInBtn.querySelector('span');
            if (icon) {
                icon.classList.toggle('fa-circle-check', enabled);
                icon.classList.toggle('fa-regular', !enabled);
                icon.classList.toggle('fa-solid', enabled);
                icon.classList.toggle('fa-circle', !enabled);
            }
            if (label) {
                label.textContent = enabled
                    ? 'Walk-in selected (tap to remove)'
                    : 'Request walk-in for today';
            }
        }

        if (helpEl && enabled) {
            helpEl.textContent = 'Walk-in request selected. No slot is reserved until staff approval.';
        }
    }

    function hideWalkInOption(){
        if (walkInBox) walkInBox.classList.add('d-none');
        setWalkInRequested(false);
    }

    function showWalkInOption(message){
        if (!walkInBox) return;
        walkInBox.classList.remove('d-none');
        if (walkInText && message) walkInText.textContent = message;
    }

    function setLoading(msg){
        timeEl.disabled = false;
        timeEl.innerHTML = `<option value="">${msg}</option>`;
        if (helpEl) helpEl.textContent = '';
        if (gridEl) gridEl.innerHTML = '';
        hideWalkInOption();
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

        gridEl.innerHTML = slots.map((t) => `
            <button type="button" class="kt-slot" data-time="${t}">
                ${fmt12h(t)}
            </button>
        `).join('');

        const btns = Array.from(gridEl.querySelectorAll('.kt-slot'));
        const markActive = (val) => btns.forEach((b) => b.classList.toggle('is-active', b.dataset.time === val));

        markActive(timeEl.value);

        btns.forEach((btn) => {
            btn.addEventListener('click', () => {
                timeEl.value = btn.dataset.time;
                markActive(btn.dataset.time);
                setWalkInRequested(false);
            });
        });
    }

    async function loadSlots(){
        const date = dateEl.value;
        const doctorId = doctorEl?.value || '';

        if (doctorRequired && doctorEl && !doctorId){
            setLoading('Select dentist first...');
            return;
        }
        if(!date){
            setLoading('Select date first...');
            return;
        }

        setLoading('Loading available times...');

        const url = new URL(`/book/${serviceId}/slots`, window.location.origin);
        url.searchParams.set('date', date);
        if (doctorRequired) url.searchParams.set('doctor_id', doctorId);

        let res;
        try {
            res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
        } catch (e) {
            setLoading('Unable to load slots');
            return;
        }

        if(!res.ok){
            setLoading('Unable to load slots');
            return;
        }

        const data = await res.json();
        const slots = data.slots || [];

        if(!slots.length){
            timeEl.disabled = true;
            timeEl.innerHTML = `<option value="">No available slots</option>`;
            if (helpEl) helpEl.textContent = 'No available schedule slots for this date.';
            if (gridEl) gridEl.innerHTML = '';

            const isTodaySelected = (date === todayIso);
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
        timeEl.innerHTML = `<option value="">Select time...</option>` + slots.map((t) => {
            const selected = (!seededOldTime && oldTime && oldTime === t) ? 'selected' : '';
            return `<option value="${t}" ${selected}>${fmt12h(t)}</option>`;
        }).join('');

        if (!seededOldTime && oldTime) {
            timeEl.value = oldTime;
            seededOldTime = true;
        }

        renderGrid(slots);

        if (helpEl){
            const suffix = (doctorRequired && doctorId) ? ' for this dentist.' : '.';
            helpEl.textContent = `${slots.length} slot(s) available${suffix}`;
        }
    }

    if (walkInBtn){
        walkInBtn.addEventListener('click', () => {
            if (walkInBox?.classList.contains('d-none')) return;
            const next = !(walkInInput?.value === '1');
            setWalkInRequested(next);
        });
    }

    timeEl.addEventListener('change', () => {
        if (timeEl.value) setWalkInRequested(false);
    });

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
