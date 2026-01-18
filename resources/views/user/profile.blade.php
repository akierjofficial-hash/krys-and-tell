@extends('layouts.public')
@section('title', 'My Profile — Krys & Tell')

@section('content')
@php
    /** Badge helper */
    $badge = function ($status) {
        $s = strtolower(trim((string)($status ?? 'pending')));

        if ($s === '' || $s === 'pending') return ['Pending', 'warning'];
        if (in_array($s, ['approved','confirmed','accepted','approved_request','approve'], true)) return ['Approved', 'success'];
        if (in_array($s, ['declined','rejected','cancelled','canceled'], true)) return ['Declined', 'danger'];
        if (in_array($s, ['done','completed','finished'], true)) return ['Completed', 'secondary'];

        return [ucfirst($s), 'secondary'];
    };

    $fmtDate = function ($d) {
        try { return $d ? \Carbon\Carbon::parse($d)->format('M d, Y') : '—'; }
        catch (\Throwable $e) { return '—'; }
    };

    $fmtTime = function ($t) {
        try {
            if (!$t) return '';
            return \Carbon\Carbon::parse($t)->format('h:i A');
        } catch (\Throwable $e) {
            return (string)$t;
        }
    };

    $doctorName = function ($a) {
        return $a->doctor->name ?? ($a->dentist_name ?? '—');
    };

    $hasLocalPassword = !empty($user->password);
@endphp

<section class="section section-soft">
    <div class="container">

        <style>
            /* ===== Mobile UX: Tabs + Cards ===== */
            .kt-profile-head{
                display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
                margin-bottom: 12px;
            }
            .kt-profile-left{ display:flex; align-items:center; gap:12px; min-width:0; }
            .kt-avatar{
                width:54px; height:54px; border-radius:18px;
                display:grid; place-items:center;
                font-weight:950; font-size:1.1rem;
                background: rgba(176,124,88,.14);
                border: 1px solid rgba(17,17,17,.10);
                flex: 0 0 auto;
            }
            .kt-name{ font-weight:950; font-size:1.05rem; line-height:1.1; margin:0; }
            .kt-email{ color: var(--muted); font-weight:650; font-size:.92rem; margin:2px 0 0 0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

            .kt-mobile-tabs{
                gap: 8px;
                background: rgba(255,255,255,.55);
                border: 1px solid var(--border);
                border-radius: 999px;
                padding: 8px;
                overflow-x:auto;
                -webkit-overflow-scrolling: touch;
            }
            .kt-mobile-tabs .nav-link{
                border-radius: 999px;
                border: 1px solid var(--border);
                font-weight: 900;
                font-size: 13px;
                padding: .55rem .95rem;
                color: var(--text);
                background: rgba(255,255,255,.75);
                white-space: nowrap;
            }
            .kt-mobile-tabs .nav-link.active{
                background: rgba(176,124,88,.18);
                border-color: rgba(176,124,88,.35);
                color: var(--text);
            }

            .kt-appt{
                border: 1px solid var(--border);
                border-radius: 18px;
                background: rgba(255,255,255,.92);
                box-shadow: 0 14px 34px rgba(15,23,42,.06);
                padding: 14px;
            }
            .kt-appt + .kt-appt{ margin-top: 10px; }
            .kt-appt-title{
                font-weight: 950;
                font-size: 14px;
                margin: 0;
            }
            .kt-appt-meta{ color: var(--muted); font-weight: 650; font-size: 12.5px; margin-top: 3px; }
            .kt-appt-sub{ font-weight: 650; font-size: 12.5px; margin-top: 6px; }
            .kt-appt-sub b{ font-weight: 900; }

            .kt-card-tight{ border: 1px solid var(--border); border-radius: 18px; background: rgba(255,255,255,.92); }
            .kt-card-tight .head{ padding: 14px 14px 10px; border-bottom: 1px solid var(--border); }
            .kt-card-tight .body{ padding: 14px; }
            .kt-help-note{
                font-size: 12.5px;
                color: var(--muted);
                font-weight: 650;
                margin-top: 8px;
                line-height: 1.45;
            }

            /* Slightly tighter for small phones */
            @media (max-width: 390px){
                .kt-mobile-tabs .nav-link{ padding: .5rem .8rem; font-size: 12.5px; }
            }
        </style>

        {{-- =========================
            MOBILE (Tabs)
        ========================= --}}
        <div class="d-lg-none">

            <div class="card-soft p-4 mb-3">
                <div class="kt-profile-head">
                    <div class="kt-profile-left">
                        <div class="kt-avatar">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div style="min-width:0;">
                            <p class="kt-name">{{ $user->name }}</p>
                            <p class="kt-email">{{ $user->email }}</p>
                        </div>
                    </div>

                    <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                        <i class="fa-solid fa-calendar-check me-1"></i> Book
                    </a>
                </div>
            </div>

            <ul class="nav nav-pills kt-mobile-tabs mb-3" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-upcoming-btn" data-bs-toggle="pill" data-bs-target="#tab-upcoming" type="button" role="tab">
                        Upcoming
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-history-btn" data-bs-toggle="pill" data-bs-target="#tab-history" type="button" role="tab">
                        History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-account-btn" data-bs-toggle="pill" data-bs-target="#tab-account" type="button" role="tab">
                        Account
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="profileTabsContent">
                {{-- Upcoming --}}
                <div class="tab-pane fade show active" id="tab-upcoming" role="tabpanel" aria-labelledby="tab-upcoming-btn">
                    <div class="kt-card-tight">
                        <div class="head">
                            <div class="sec-title" style="font-size:1.05rem;margin:0;">Upcoming Schedule</div>
                            <div class="sec-sub" style="margin:4px 0 0;">Your approved/confirmed appointments will show here.</div>
                        </div>
                        <div class="body">
                            @forelse($upcoming as $a)
                                @php
                                    [$label, $type] = $badge($a->status);
                                    $d = $fmtDate($a->appointment_date);
                                    $t = $fmtTime($a->appointment_time);
                                    $svc = $a->service->name ?? 'Service';
                                    $doc = $doctorName($a);
                                @endphp

                                <div class="kt-appt">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <p class="kt-appt-title">{{ $svc }}</p>
                                        <span class="badge bg-{{ $type }}" style="border-radius:999px;font-weight:900;">
                                            {{ $label }}
                                        </span>
                                    </div>

                                    <div class="kt-appt-meta">
                                        <i class="fa-regular fa-calendar me-1"></i> {{ $d }}
                                        @if($t) • <i class="fa-regular fa-clock me-1"></i> {{ $t }} @endif
                                    </div>

                                    <div class="kt-appt-sub">
                                        <b>Doctor:</b> {{ $doc }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3">
                                    <div style="font-weight:950;">No upcoming appointments</div>
                                    <div class="kt-help-note">Book a service and wait for staff approval.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- History --}}
                <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="tab-history-btn">
                    <div class="kt-card-tight">
                        <div class="head">
                            <div class="sec-title" style="font-size:1.05rem;margin:0;">Booking History</div>
                            <div class="sec-sub" style="margin:4px 0 0;">All of your booking requests and updates.</div>
                        </div>
                        <div class="body">
                            @forelse($history as $a)
                                @php
                                    [$label, $type] = $badge($a->status);
                                    $d = $fmtDate($a->appointment_date);
                                    $t = $fmtTime($a->appointment_time);
                                    $svc = $a->service->name ?? 'Service';
                                    $doc = $doctorName($a);
                                @endphp

                                <div class="kt-appt">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <p class="kt-appt-title">{{ $svc }}</p>
                                        <span class="badge bg-{{ $type }}" style="border-radius:999px;font-weight:900;">
                                            {{ $label }}
                                        </span>
                                    </div>

                                    <div class="kt-appt-meta">
                                        <i class="fa-regular fa-calendar me-1"></i> {{ $d }}
                                        @if($t) • <i class="fa-regular fa-clock me-1"></i> {{ $t }} @endif
                                    </div>

                                    <div class="kt-appt-sub">
                                        <b>Doctor:</b> {{ $doc }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3">
                                    <div style="font-weight:950;">No bookings yet</div>
                                    <div class="kt-help-note">Start by booking a service from the Services page.</div>
                                </div>
                            @endforelse

                            @if(method_exists($history, 'links'))
                                <div class="mt-3">
                                    {{ $history->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Account --}}
                <div class="tab-pane fade" id="tab-account" role="tabpanel" aria-labelledby="tab-account-btn">

                    <div class="accordion" id="acctAccordion">

                        {{-- Settings --}}
                        <div class="accordion-item" style="border:1px solid var(--border);border-radius:18px;overflow:hidden;background:rgba(255,255,255,.92);">
                            <h2 class="accordion-header" id="acctHead1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#acctCollapse1" aria-expanded="true">
                                    Profile Settings
                                </button>
                            </h2>
                            <div id="acctCollapse1" class="accordion-collapse collapse show" aria-labelledby="acctHead1" data-bs-parent="#acctAccordion">
                                <div class="accordion-body">
                                    <form method="POST" action="{{ route('user.profile.update') }}" class="mt-1">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-3">
                                            <label class="kt-field">Name</label>
                                            <input type="text" class="kt-input" name="name" value="{{ old('name', $user->name) }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="kt-field">Email</label>
                                            <input type="email" class="kt-input" name="email" value="{{ old('email', $user->email) }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="kt-field d-block">Reminders</label>

                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" role="switch" id="notify24" name="notify_24h" value="1"
                                                       {{ old('notify_24h', $user->notify_24h) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notify24">24-hour reminder</label>
                                            </div>

                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="notify1" name="notify_1h" value="1"
                                                       {{ old('notify_1h', $user->notify_1h) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notify1">1-hour reminder</label>
                                            </div>

                                            <div class="kt-help-note">
                                                Note: These are your reminder preferences. Reminder sending depends on the reminder job/scheduler being enabled on the server.
                                            </div>
                                        </div>

                                        <button class="btn kt-btn kt-btn-primary text-white w-100" type="submit">
                                            <i class="fa-solid fa-floppy-disk me-1"></i> Save changes
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="accordion-item mt-3" style="border:1px solid var(--border);border-radius:18px;overflow:hidden;background:rgba(255,255,255,.92);">
                            <h2 class="accordion-header" id="acctHead2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acctCollapse2">
                                    {{ $hasLocalPassword ? 'Change Password' : 'Set Password' }}
                                </button>
                            </h2>
                            <div id="acctCollapse2" class="accordion-collapse collapse" aria-labelledby="acctHead2" data-bs-parent="#acctAccordion">
                                <div class="accordion-body">
                                    @if(!$hasLocalPassword)
                                        <div class="kt-help-note mb-2">
                                            You signed in with Google. Set a password to allow logging in with email + password too.
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('user.profile.password') }}" class="mt-1">
                                        @csrf

                                        @if($hasLocalPassword)
                                            <div class="mb-3">
                                                <label class="kt-field">Current Password</label>
                                                <input type="password" class="kt-input" name="current_password" required>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="kt-field">New Password</label>
                                            <input type="password" class="kt-input" name="password" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="kt-field">Confirm New Password</label>
                                            <input type="password" class="kt-input" name="password_confirmation" required>
                                        </div>

                                        <button class="btn kt-btn kt-btn-primary text-white w-100" type="submit">
                                            <i class="fa-solid fa-key me-1"></i>
                                            {{ $hasLocalPassword ? 'Update password' : 'Set password' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        {{-- =========================
            DESKTOP (2-column)
        ========================= --}}
        <div class="d-none d-lg-block">
            <div class="row g-4 align-items-start">
                <!-- Left: Upcoming -->
                <div class="col-lg-4">
                    <div class="card-soft p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="kt-avatar" style="width:56px;height:56px;font-size:1.2rem;">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div style="min-width:0;">
                                <div style="font-weight:950;font-size:1.15rem;line-height:1.1;">{{ $user->name }}</div>
                                <div class="text-muted-2" style="font-weight:650;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </div>

                        <hr style="border-color: rgba(17,17,17,.10);">

                        <div class="d-flex align-items-center justify-content-between">
                            <div style="font-weight:950;">Upcoming Schedule</div>
                            <a href="{{ url('/services') }}" class="btn kt-btn kt-btn-outline btn-sm">Book</a>
                        </div>

                        <div class="mt-3 d-grid gap-2">
                            @forelse($upcoming as $a)
                                @php
                                    [$label, $type] = $badge($a->status);
                                @endphp
                                <div class="kt-appt">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div style="min-width:0;">
                                            <div style="font-weight:950;font-size:13px;">
                                                {{ $a->service->name ?? 'Service' }}
                                            </div>
                                            <div class="text-muted-2" style="font-weight:650;font-size:12px;">
                                                {{ $fmtDate($a->appointment_date) }}
                                                @if($a->appointment_time) • {{ $fmtTime($a->appointment_time) }} @endif
                                            </div>
                                            <div class="text-muted-2" style="font-weight:650;font-size:12px;">
                                                Doctor: {{ $doctorName($a) }}
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $type }}" style="border-radius:999px;font-weight:900;">
                                            {{ $label }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted-2" style="font-weight:650;">No upcoming appointments.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Right: Settings + Password + History -->
                <div class="col-lg-8">
                    <!-- Profile Settings -->
                    <div class="card-soft p-4">
                        <div class="sec-title" style="font-size:1.25rem;">Profile Settings</div>
                        <div class="sec-sub">Update your account info and reminder preferences.</div>

                        <form method="POST" action="{{ route('user.profile.update') }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="kt-field">Name</label>
                                    <input type="text" class="kt-input" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="kt-field">Email</label>
                                    <input type="email" class="kt-input" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="kt-field d-block">Reminders</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="dNotify24" name="notify_24h" value="1"
                                               {{ old('notify_24h', $user->notify_24h) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dNotify24">24-hour reminder</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="dNotify1" name="notify_1h" value="1"
                                               {{ old('notify_1h', $user->notify_1h) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dNotify1">1-hour reminder</label>
                                    </div>
                                </div>
                                <div class="kt-help-note">
                                    Note: These are your reminder preferences. Reminder sending depends on the reminder job/scheduler being enabled on the server.
                                </div>
                            </div>

                            <button class="btn kt-btn kt-btn-primary text-white mt-3" type="submit">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save changes
                            </button>
                        </form>
                    </div>

                    <!-- Change/Set Password -->
                    <div class="card-soft p-4 mt-3">
                        <div class="sec-title" style="font-size:1.25rem;">
                            {{ $hasLocalPassword ? 'Change Password' : 'Set Password' }}
                        </div>
                        <div class="sec-sub">
                            {{ $hasLocalPassword ? 'Keep your account secure.' : 'Set a password to allow email + password login.' }}
                        </div>

                        <form method="POST" action="{{ route('user.profile.password') }}" class="mt-3">
                            @csrf

                            <div class="row g-3">
                                @if($hasLocalPassword)
                                    <div class="col-md-4">
                                        <label class="kt-field">Current Password</label>
                                        <input type="password" class="kt-input" name="current_password" required>
                                    </div>
                                @endif

                                <div class="{{ $hasLocalPassword ? 'col-md-4' : 'col-md-6' }}">
                                    <label class="kt-field">New Password</label>
                                    <input type="password" class="kt-input" name="password" required>
                                </div>
                                <div class="{{ $hasLocalPassword ? 'col-md-4' : 'col-md-6' }}">
                                    <label class="kt-field">Confirm New Password</label>
                                    <input type="password" class="kt-input" name="password_confirmation" required>
                                </div>
                            </div>

                            <button class="btn kt-btn kt-btn-primary text-white mt-3" type="submit">
                                <i class="fa-solid fa-key me-1"></i>
                                {{ $hasLocalPassword ? 'Update password' : 'Set password' }}
                            </button>
                        </form>
                    </div>

                    <!-- History (table on desktop) -->
                    <div class="card-soft p-4 mt-3">
                        <div class="sec-title" style="font-size:1.25rem;">Booking History</div>
                        <div class="sec-sub">All of your booking requests.</div>

                        <div class="table-responsive mt-3">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history as $a)
                                        @php [$label, $type] = $badge($a->status); @endphp
                                        <tr>
                                            <td>{{ $a->service->name ?? 'Service' }}</td>
                                            <td>{{ $fmtDate($a->appointment_date) }}</td>
                                            <td>{{ $fmtTime($a->appointment_time) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $type }}" style="border-radius:999px;font-weight:900;">
                                                    {{ $label }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($history, 'links'))
                            {{ $history->links() }}
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>
@endsection
