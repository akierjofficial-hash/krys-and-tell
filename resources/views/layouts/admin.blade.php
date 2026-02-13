<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <title>Krys&Tell — Admin</title>

    {{-- ✅ PWA (Installable App) --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#B07C58">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Krys&Tell">
    <link rel="apple-touch-icon" href="/images/pwa/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/pwa/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/pwa/icon-512.png">

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    /* ==========================================================
       ✅ Push Button Premium State (ON)
       Works if JS toggles: .is-on OR your existing .kt-push-enabled
       ========================================================== */

    #ktPushBtn { position: relative; overflow: visible; }

    /* Keep existing compatibility */
    .kt-push-enabled { box-shadow: 0 0 0 4px rgba(34,197,94,.18) !important; }

    /* Premium ON styling */
    #ktPushBtn.is-on,
    #ktPushBtn.kt-push-enabled{
        background: rgba(34,197,94,.12) !important;
        border-color: rgba(34,197,94,.25) !important;
        box-shadow: 0 0 0 0 rgba(34,197,94,.45) !important;
        animation: ktPulse 1.6s infinite;
    }

    #ktPushBtn.is-on::after,
    #ktPushBtn.kt-push-enabled::after{
        content:'';
        position:absolute;
        top:6px; right:6px;
        width:9px; height:9px;
        border-radius:999px;
        background:#22c55e;
        box-shadow:0 0 0 6px rgba(34,197,94,.20);
        pointer-events:none;
    }

    @keyframes ktPulse{
        0%   { transform: scale(1);   box-shadow:0 0 0 0 rgba(34,197,94,.45); }
        60%  { transform: scale(1.05);box-shadow:0 0 0 14px rgba(34,197,94,0); }
        100% { transform: scale(1);   box-shadow:0 0 0 0 rgba(34,197,94,0); }
    }

    @media (prefers-reduced-motion: reduce){
        #ktPushBtn.is-on,
        #ktPushBtn.kt-push-enabled{ animation:none !important; }
        #ktPushBtn.is-on::after,
        #ktPushBtn.kt-push-enabled::after{ box-shadow:none !important; }
    }

    :root {
        --primary: #155AC1;
        --primary-2: #759EDB;

        --bg: #E5ECF7;
        --surface: rgba(255, 255, 255, .86);
        --surface-solid: #FFFFFF;

        --text: #0f172a;
        --muted: rgba(15, 23, 42, .58);

        --border: rgba(15, 23, 42, .10);

        --card-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        --radius: 18px;

        /* table tokens */
        --table-head: rgba(229, 236, 247, .55);
        --table-row-hover: rgba(21, 90, 193, .06);
    }

    html[data-theme="dark"] {
        color-scheme: dark;

        --bg: #0b1220;
        --surface: rgba(17, 24, 39, .88);
        --surface-solid: rgba(17, 24, 39, .92);

        --text: #f8fafc;
        --muted: rgba(248, 250, 252, .72);

        --border: rgba(148, 163, 184, .18);
        --card-shadow: 0 18px 48px rgba(0, 0, 0, .55);

        --table-head: rgba(2, 6, 23, .75);
        --table-row-hover: rgba(148, 163, 184, .10);
    }

    body {
        margin: 0;
        font-family: "Nunito Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
    }

    /* soft background like dashboard */
    .app-bg {
        min-height: 100vh;
        background:
            radial-gradient(900px 440px at 12% -10%, rgba(59, 130, 246, .14), transparent 60%),
            radial-gradient(900px 440px at 92% 12%, rgba(124, 58, 237, .10), transparent 55%),
            radial-gradient(900px 520px at 40% 110%, rgba(34, 197, 94, .10), transparent 55%),
            var(--bg);
    }

    .shell {
        display: flex;
        min-height: 100vh;
    }

    /* ===== Sidebar ===== */
    .side {
        width: 270px;
        background: var(--surface);
        border-right: 1px solid var(--border);
        padding: 18px 14px;
        position: sticky;
        top: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .brand {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        padding: 10px 10px 14px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 10px;
    }

    .brand-left {
        display:flex;
        align-items:center;
        gap:10px;
        min-width:0;
    }

    .logo {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-weight: 950;
        letter-spacing: -.4px;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        box-shadow: 0 14px 24px rgba(15, 23, 42, .18);
        flex: 0 0 auto;
    }

    .brand .name {
        font-weight: 950;
        letter-spacing: -.2px;
        margin: 0;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .brand .sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .theme-toggle {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, .75);
        color: var(--text);
        display: grid;
        place-items: center;
        box-shadow: 0 10px 18px rgba(15, 23, 42, .10);
        cursor: pointer;
        transition: .15s ease;
        flex: 0 0 auto;
    }

    html[data-theme="dark"] .theme-toggle { background: rgba(2, 6, 23, .35) !important; }
    .theme-toggle:hover { transform: translateY(-1px); }

    .navx {
        margin-top: 12px;
        display:flex;
        flex-direction:column;
        gap:6px;
        padding:0 6px;
        flex:1;
        overflow-y:auto;
        min-height:0;
    }

    .navx::-webkit-scrollbar { width: 8px; }
    .navx::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, .18); border-radius: 999px; }
    .navx::-webkit-scrollbar-track { background: transparent; }

    .navx a {
        text-decoration: none;
        display:flex;
        align-items:center;
        gap:10px;
        padding: 11px 12px;
        border-radius: 14px;
        color: var(--text);
        font-weight: 900;
        transition: .15s ease;
        position: relative;
        overflow: hidden;
    }

    .navx a i { width: 18px; color: var(--muted); }
    .navx a:hover { background: rgba(37, 99, 235, .08); transform: translateY(-1px); }

    .navx a.active {
        background: linear-gradient(135deg, rgba(37, 99, 235, .95), rgba(124, 58, 237, .85));
        color: #fff;
        box-shadow: 0 14px 26px rgba(37, 99, 235, .18);
    }

    .navx a.active i { color: #fff; }

    .side-footer {
        margin-top:auto;
        padding: 12px 6px 0;
        border-top: 1px solid var(--border);
    }

    .logout-btn {
        width:100%;
        border-radius:14px;
        font-weight:950;
        padding:10px 12px;
    }

    /* ===== Main ===== */
    .main { flex:1; padding: 18px 20px 26px; min-width:0; }

    .cardx{
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(10px);
    }

    /* Forms dark-safe */
    html[data-theme="dark"] .form-control,
    html[data-theme="dark"] .form-select,
    html[data-theme="dark"] input[type="date"],
    html[data-theme="dark"] select {
        background: rgba(2, 6, 23, .65) !important;
        border-color: var(--border) !important;
        color: var(--text) !important;
    }

    html[data-theme="dark"] select option {
        background: rgba(2, 6, 23, .95) !important;
        color: var(--text) !important;
    }

    /* Tables dark-safe */
    .table { color: var(--text) !important; }
    .table> :not(caption)>*>* { background-color: transparent !important; color: inherit !important; border-color: var(--border) !important; }

    .table thead th {
        background: var(--table-head) !important;
        color: var(--muted) !important;
        font-weight: 950 !important;
        border-color: var(--border) !important;
    }

    html[data-theme="dark"] .table tbody tr:hover { background: var(--table-row-hover) !important; }

    /* FullCalendar dark-safe */
    html[data-theme="dark"] .fc,
    html[data-theme="dark"] .fc .fc-scrollgrid,
    html[data-theme="dark"] .fc .fc-view-harness {
        background: transparent !important;
        color: var(--text) !important;
        border-color: var(--border) !important;
    }

    html[data-theme="dark"] .fc-theme-standard td,
    html[data-theme="dark"] .fc-theme-standard th { border-color: rgba(148, 163, 184, .18) !important; }

    html[data-theme="dark"] .fc .fc-toolbar-title { color: var(--text) !important; }

    html[data-theme="dark"] .fc .fc-button {
        background: rgba(17, 24, 39, .92) !important;
        border-color: rgba(148, 163, 184, .22) !important;
        color: var(--text) !important;
        border-radius: 12px !important;
        font-weight: 950 !important;
    }

    html[data-theme="dark"] .fc .fc-day-today { background: rgba(96, 165, 250, .10) !important; }

    /* ===== Mobile Responsive Sidebar Drawer ===== */
    .side-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .35);
        z-index: 1999;
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease;
    }

    .side-overlay.show { opacity: 1; pointer-events: auto; }

    .menu-toggle {
        display: none;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: var(--surface);
        box-shadow: 0 10px 18px rgba(15, 23, 42, .10);
        place-items: center;
        cursor: pointer;
    }

    @media (max-width: 900px) {
        .menu-toggle { display: grid; }

        .side {
            position: fixed;
            left: 0;
            top: 0;
            height: 100dvh;
            z-index: 2000;
            transform: translateX(-105%);
            transition: transform .18s ease;
        }

        .side.open { transform: translateX(0); }
        .main { padding: 14px 14px 18px; }
    }

    /* ----------------------------------------------------------
       ✅ Approval bell (Admin) — same UX as Staff
       ---------------------------------------------------------- */
    .kt-top-icon{
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        transition: .15s ease;
    }

    .kt-top-icon:hover{ background: rgba(255,255,255,.06); }

    .kt-dot{
        position:absolute;
        top: 9px;
        right: 9px;
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background:#ef4444;
        box-shadow: 0 0 0 2px rgba(255,255,255,.95);
    }

    html[data-theme="dark"] .kt-dot{ box-shadow: 0 0 0 2px rgba(2,6,23,.85); }

    .kt-popover{
        position:absolute;
        top: 54px;
        right: 0;
        width: 380px;
        max-width: calc(100vw - 24px);
        border-radius: 16px;
        background: var(--surface);
        border: 1px solid var(--border);
        box-shadow: var(--card-shadow);
        z-index: 2500;
        overflow:hidden;

        opacity:0;
        transform: translateY(-8px) scale(.98);
        pointer-events:none;
        visibility:hidden;
        transition: opacity 160ms ease, transform 180ms cubic-bezier(.2,.8,.2,1), visibility 0s linear 180ms;
    }

    .kt-popover.show{
        opacity:1;
        transform: translateY(0) scale(1);
        pointer-events:auto;
        visibility:visible;
        transition: opacity 160ms ease, transform 180ms cubic-bezier(.2,.8,.2,1), visibility 0s;
    }

    .kt-popover .kt-pop-h{
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
    }

    .kt-popover .kt-pop-title{ font-weight: 900; font-size: 14px; margin:0; }

    .kt-popover .kt-badge{
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(13,110,253,.12);
        border: 1px solid rgba(13,110,253,.22);
        color: var(--text);
    }

    .kt-popover .kt-pop-body{ padding: 10px; max-height: 360px; overflow:auto; }

    .kt-popover .kt-item{
        border: 1px solid var(--border);
        background: var(--surface);
        border-radius: 14px;
        padding: 10px 12px;
        margin-bottom: 10px;
    }

    .kt-popover .kt-item:last-child{ margin-bottom:0; }

    .kt-popover .kt-item .top{ display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
    .kt-popover .kt-item .name{ font-weight: 900; font-size: 13px; margin:0; line-height:1.2; }
    .kt-popover .kt-item .meta{ font-size: 12px; opacity: .95; margin-top: 4px; }

    .kt-popover .kt-actions{ display:flex; gap:8px; margin-top: 10px; }
    .kt-popover .kt-actions form{ margin:0; }

    .kt-popover .btn-mini{ padding: 6px 10px; border-radius: 10px; font-weight: 800; font-size: 12px; }
    .kt-popover .btn-approve{ background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.25); color: #16a34a !important; }
    .kt-popover .btn-decline{ background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.25); color: #ef4444 !important; }

    .kt-nav-badge{
        margin-left:auto;
        min-width: 20px;
        height: 18px;
        padding: 0 6px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size: 11px;
        font-weight: 950;
        border-radius: 999px;
        background: rgba(239, 68, 68, .22);
        border: 1px solid rgba(239, 68, 68, .35);
        color: #fff;
        line-height: 1;
    }
    </style>

    @stack('styles')
</head>

<body data-kt-live-scope="@yield('kt_live_scope')"
      data-kt-live-snapshot-url="{{ route('admin.live.snapshot') }}"
      data-kt-live-interval="@yield('kt_live_interval', 10000)">

    @php
        // ✅ Approval requests for bell + sidebar badge
        $pendingApprovals = 0;
        $pendingItems = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('appointments')
                && \Illuminate\Support\Facades\Schema::hasColumn('appointments', 'status')) {

                $pendingItems = \App\Models\Appointment::query()
                    ->with(['service','doctor','patient'])
                    ->where('status', 'pending')
                    ->orderByDesc('created_at')
                    ->take(8)
                    ->get();

                $pendingApprovals = $pendingItems->count();
            }
        } catch (\Throwable $e) {
            $pendingApprovals = 0;
            $pendingItems = collect();
        }
    @endphp

    <div class="app-bg">
        <div class="shell">

            {{-- mobile overlay --}}
            <div class="side-overlay" id="sideOverlay"></div>

            <aside class="side" id="adminSidebar">
                <div class="brand">
                    <div class="brand-left">
                        <div class="logo">KT</div>
                        <div style="min-width:0;">
                            <div class="name">Krys & Tell</div>
                            <div class="sub">Admin Panel</div>
                        </div>
                    </div>

                    {{-- dark mode toggle beside brand --}}
                    <button class="theme-toggle" id="themeToggle" type="button" title="Toggle Dark Mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                </div>

                <div class="navx">
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa fa-chart-line"></i> Dashboard
                    </a>

                    <a href="{{ route('admin.analytics.index') }}"
                        class="{{ request()->is('admin/analytics*') ? 'active' : '' }}">
                        <i class="fa fa-chart-pie"></i> Analytics
                    </a>

                    <a href="{{ route('admin.schedule.index') }}"
                        class="{{ request()->is('admin/schedule*') ? 'active' : '' }}">
                        <i class="fa fa-calendar-check"></i> Schedule
                    </a>

                    <a href="{{ route('admin.appointments.index') }}"
                        class="{{ request()->is('admin/appointments*') ? 'active' : '' }}">
                        <i class="fa fa-calendar-days"></i> Appointments
                    </a>

                    <a href="{{ route('admin.approvals.index') }}"
                        class="{{ request()->is('admin/approvals*') ? 'active' : '' }}">
                        <i class="fa fa-bell"></i> Approval Requests
                        <span id="adminApprovalNavBadge"
                              class="kt-nav-badge {{ $pendingApprovals > 0 ? '' : 'd-none' }}">{{ $pendingApprovals }}</span>
                    </a>

                    <a href="{{ route('admin.patients.index') }}"
                        class="{{ request()->is('admin/patients*') ? 'active' : '' }}">
                        <i class="fa fa-users"></i> Patients
                    </a>

                    <a href="{{ route('admin.doctors.index') }}"
                        class="{{ request()->is('admin/doctors*') ? 'active' : '' }}">
                        <i class="fa fa-user-doctor"></i> Doctors
                    </a>

                    <a href="{{ route('admin.user_accounts.index') }}"
                        class="{{ request()->routeIs('admin.user_accounts.*') ? 'active' : '' }}">
                        <i class="fa fa-user"></i> Users
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                        <i class="fa fa-user-shield"></i> Staff Accounts
                    </a>
                </div>

                <div class="side-footer">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary logout-btn">
                            <i class="fa fa-right-from-bracket me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="main">
                {{-- Top bar: menu (left) + approval bell (right) --}}
                <div class="d-flex align-items-center mb-3">
                    <button class="menu-toggle" id="menuToggle" type="button" title="Menu">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="ms-auto d-flex align-items-center gap-2 position-relative">
                        {{-- ✅ Push notifications (PWA) — ALWAYS BULLHORN --}}
                        <button type="button" id="ktPushBtn" class="kt-top-icon border-0" title="Enable push notifications">
                            <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                        </button>

                        {{-- Bell button --}}
                        <button type="button" id="adminApprovalBell" class="kt-top-icon position-relative border-0"
                            title="Approval Requests" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-bell"></i>
                            <span id="adminApprovalDot" class="kt-dot {{ $pendingApprovals > 0 ? '' : 'd-none' }}"></span>
                        </button>

                        {{-- Dropdown card --}}
                        <div id="adminApprovalPopover" class="kt-popover" aria-hidden="true">
                            <div class="kt-pop-h">
                                <p class="kt-pop-title mb-0">Approval Requests</p>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('admin.approvals.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:999px;font-weight:800;">View all</a>
                                    <span class="kt-badge">
                                        <span id="adminApprovalBadge">{{ $pendingApprovals }}</span> pending
                                    </span>
                                </div>
                            </div>

                            {{-- Flash message --}}
                            <div id="adminApprovalFlash" class="px-3 pt-3 d-none"></div>

                            <div class="kt-pop-body" id="adminApprovalList">
                                @if($pendingItems->isEmpty())
                                    <div class="text-center py-3" id="adminApprovalEmpty">
                                        <div class="fw-bold">No pending requests</div>
                                        <div class="small text-muted">You're all caught up.</div>
                                    </div>
                                @else
                                    @foreach($pendingItems as $a)
                                        @php
                                            $displayName =
                                                $a->public_name
                                                ?? trim(($a->public_first_name ?? '').' '.($a->public_middle_name ?? '').' '.($a->public_last_name ?? ''))
                                                ?: ($a->patient->name ?? 'Patient');

                                            $serviceName = $a->service->name ?? 'Service';
                                            $doctorName = $a->doctor->name ?? ($a->dentist_name ?? 'Doctor');

                                            $date = $a->appointment_date ?? null;
                                            $time = $a->appointment_time ?? null;
                                        @endphp

                                        <div class="kt-item" data-approval-id="{{ $a->id }}">
                                            <div class="top">
                                                <div>
                                                    <p class="name">{{ $displayName }}</p>
                                                    <div class="meta">
                                                        <div><b>{{ $serviceName }}</b></div>
                                                        <div>
                                                            {{ $date ? \Carbon\Carbon::parse($date)->format('M d, Y') : '—' }}
                                                            @if($time) • {{ \Carbon\Carbon::parse($time)->format('h:i A') }} @endif
                                                        </div>
                                                        <div class="small text-muted">Doctor: {{ $doctorName }}</div>
                                                    </div>
                                                </div>

                                                <div class="small text-muted text-end">
                                                    {{ optional($a->created_at)->diffForHumans() }}
                                                </div>
                                            </div>

                                            <div class="kt-actions">
                                                <form class="approval-form" data-action="approve" method="POST"
                                                    action="{{ route('admin.approvals.approve', $a->id) }}">
                                                    @csrf
                                                    <button class="btn btn-mini btn-approve" type="submit">
                                                        <i class="fa-solid fa-check me-1"></i> Approve
                                                    </button>
                                                </form>

                                                <form class="approval-form" data-action="decline" method="POST"
                                                    action="{{ route('admin.approvals.decline', $a->id) }}">
                                                    @csrf
                                                    <button class="btn btn-mini btn-decline" type="submit">
                                                        <i class="fa-solid fa-xmark me-1"></i> Decline
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @yield('content')
            </main>
        </div>
    </div>


    <x-undo-bar />

    <script src="{{ asset('js/kt-live.js') }}?v=1"></script>
    <script src="{{ asset('js/kt-push.js') }}?v=1"></script>

    <script>
    (function() {
        const html = document.documentElement;
        const btnTheme = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('admin_theme', theme);

            if (icon) {
                icon.classList.remove('fa-moon', 'fa-sun');
                icon.classList.add(theme === 'dark' ? 'fa-sun' : 'fa-moon');
            }
        }

        const saved = localStorage.getItem('admin_theme') || 'light';
        applyTheme(saved);

        btnTheme?.addEventListener('click', function() {
            const next = (html.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
            applyTheme(next);
        });

        // sidebar drawer (mobile)
        const side = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sideOverlay');
        const btnMenu = document.getElementById('menuToggle');

        function closeSidebar() {
            side?.classList.remove('open');
            overlay?.classList.remove('show');
        }

        btnMenu?.addEventListener('click', () => {
            side?.classList.toggle('open');
            overlay?.classList.toggle('show');
        });

        overlay?.addEventListener('click', closeSidebar);

        window.addEventListener('resize', () => {
            if (!window.matchMedia('(max-width: 900px)').matches) closeSidebar();
        });

        // =========================
        // ✅ Approval popover (bell) + AJAX approve/decline + LIVE polling
        // =========================
        const bell = document.getElementById('adminApprovalBell');
        const pop = document.getElementById('adminApprovalPopover');
        const badgeEl = document.getElementById('adminApprovalBadge');
        const dotEl = document.getElementById('adminApprovalDot');
        const navBadgeEl = document.getElementById('adminApprovalNavBadge');
        const flashEl = document.getElementById('adminApprovalFlash');
        const listEl = document.getElementById('adminApprovalList');

        const csrf =
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            document.querySelector('input[name="_token"]')?.value ||
            '';

        const widgetUrl = @json(route('admin.approvals.widget'));

        function escapeHtml(str) {
            return (str ?? '').toString()
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function setCount(n) {
            n = Number(n || 0);
            if (badgeEl) badgeEl.textContent = String(n);
            if (dotEl) dotEl.classList.toggle('d-none', n <= 0);
            if (navBadgeEl) {
                navBadgeEl.textContent = String(n);
                navBadgeEl.classList.toggle('d-none', n <= 0);
            }
        }

        function showFlash(type, text) {
            if (!flashEl) return;
            flashEl.classList.remove('d-none');
            flashEl.innerHTML = `
                <div class="alert alert-${type} py-2 px-3 mb-0" style="font-size:13px; border-radius:14px;">
                    ${escapeHtml(text)}
                </div>
            `;
            window.clearTimeout(showFlash._t);
            showFlash._t = window.setTimeout(() => flashEl.classList.add('d-none'), 2500);
        }

        function ensureEmptyState() {
            if (!listEl) return;
            const anyItem = listEl.querySelector('.kt-item');
            if (!anyItem) {
                listEl.innerHTML = `
                    <div class="text-center py-3" id="adminApprovalEmpty">
                        <div class="fw-bold">No pending requests</div>
                        <div class="small text-muted">You're all caught up.</div>
                    </div>
                `;
            }
        }

        async function postAction(form) {
            const item = form.closest('.kt-item');
            const action = form.dataset.action || 'approve';
            const btns = item ? item.querySelectorAll('button') : form.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);

            try {
                const body = new URLSearchParams();
                body.set('_token', csrf);

                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok || data.ok === false) {
                    showFlash('danger', data.message || 'Action failed. Please try again.');
                    btns.forEach(b => b.disabled = false);
                    return;
                }

                showFlash('success', data.message || (action === 'approve' ? 'Booking approved.' : 'Booking declined.'));
                if (item) item.remove();

                if (typeof data.pendingCount !== 'undefined') setCount(data.pendingCount);
                else {
                    const current = Number(badgeEl?.textContent || 0);
                    setCount(Math.max(0, current - 1));
                }

                ensureEmptyState();
            } catch (e) {
                showFlash('danger', 'Network error. Please try again.');
                btns.forEach(b => b.disabled = false);
            }
        }

        function closePopover() {
            if (!pop) return;
            pop.classList.remove('show');
            pop.setAttribute('aria-hidden', 'true');
            bell?.setAttribute('aria-expanded', 'false');
        }

        function togglePopover(e) {
            e?.stopPropagation();
            if (!pop) return;
            const isOpen = pop.classList.contains('show');
            if (isOpen) closePopover();
            else {
                pop.classList.add('show');
                pop.setAttribute('aria-hidden', 'false');
                bell?.setAttribute('aria-expanded', 'true');
            }
        }

        pop?.addEventListener('submit', function(e) {
            const form = e.target.closest('form.approval-form');
            if (!form) return;
            e.preventDefault();
            e.stopPropagation();
            postAction(form);
        });

        bell?.addEventListener('click', togglePopover);
        pop?.addEventListener('click', (e) => e.stopPropagation());
        document.addEventListener('click', closePopover);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePopover(); });

        // ----- live polling -----
        let lastPending = Number(badgeEl?.textContent || 0);
        let polling = false;

        function renderItems(items) {
            if (!listEl) return;
            if (!Array.isArray(items) || items.length === 0) {
                ensureEmptyState();
                return;
            }

            const htmlItems = items.map(i => {
                const id = Number(i.id || 0);
                const patient = escapeHtml(i.patient || 'N/A');
                const service = escapeHtml(i.service || 'N/A');
                const doctor  = escapeHtml(i.doctor  || '—');
                const date    = escapeHtml(i.date    || '—');
                const time    = escapeHtml(i.time    || '—');
                const approveUrl = escapeHtml(i.approve_url || '');
                const declineUrl = escapeHtml(i.decline_url || '');

                return `
                    <div class="kt-item" data-approval-id="${id}">
                        <div class="top">
                            <div>
                                <p class="name">${patient}</p>
                                <div class="meta">
                                    <div><b>${service}</b></div>
                                    <div>${date} • ${time}</div>
                                    <div class="small text-muted">Doctor: ${doctor}</div>
                                </div>
                            </div>

                            <div class="small text-muted text-end">Pending</div>
                        </div>

                        <div class="kt-actions">
                            <form class="approval-form" data-action="approve" method="POST" action="${approveUrl}">
                                <button class="btn btn-mini btn-approve" type="submit">
                                    <i class="fa-solid fa-check me-1"></i> Approve
                                </button>
                            </form>

                            <form class="approval-form" data-action="decline" method="POST" action="${declineUrl}">
                                <button class="btn btn-mini btn-decline" type="submit">
                                    <i class="fa-solid fa-xmark me-1"></i> Decline
                                </button>
                            </form>
                        </div>
                    </div>
                `;
            }).join('');

            listEl.innerHTML = htmlItems;
        }

        async function pollApprovals() {
            if (polling) return;
            if (document.hidden) return;

            polling = true;
            try {
                const res = await fetch(widgetUrl + '?limit=8', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store'
                });

                if (!res.ok) throw new Error('poll failed');

                const data = await res.json().catch(() => ({}));
                const pendingCount = Number(data.pendingCount || 0);
                setCount(pendingCount);
                renderItems(data.items || []);

                if (pendingCount > lastPending) {
                    bell?.style.setProperty('box-shadow', '0 0 0 4px rgba(34,197,94,.18)');
                    setTimeout(() => bell?.style.removeProperty('box-shadow'), 600);
                }

                lastPending = pendingCount;
            } catch (e) {
                // silent
            } finally {
                polling = false;
            }
        }

        pollApprovals();
        setInterval(pollApprovals, 5000);

    })();
    </script>

    @stack('scripts')

    {{-- ✅ PWA Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
            });
        }
    </script>

    <script>
        // ✅ ADMIN: Push bind + force bullhorn icon ALWAYS (on/off)
        (() => {
            const btn = document.getElementById('ktPushBtn');
            if (!btn) return;

            // bind once (requires user click)
            if (window.KTPush) {
                window.KTPush.bind('#ktPushBtn');
            }

            let fixing = false;

            const ensureBullhorn = () => {
                if (fixing) return;
                fixing = true;

                try {
                    // remove any injected icon wrappers
                    btn.querySelectorAll('svg, span.fa-layers').forEach(n => n.remove());

                    // ensure exactly one <i>
                    let icon = btn.querySelector('i');
                    if (!icon) {
                        icon = document.createElement('i');
                        btn.prepend(icon);
                    }
                    btn.querySelectorAll('i').forEach((n, idx) => { if (idx > 0) n.remove(); });

                    // force bullhorn classes (do NOT touch button classes)
                    icon.className = 'fa-solid fa-bullhorn';
                    icon.setAttribute('aria-hidden', 'true');
                } finally {
                    fixing = false;
                }
            };

            ensureBullhorn();

            const obs = new MutationObserver(() => {
                if (fixing) return;
                requestAnimationFrame(ensureBullhorn);
            });

            // watch DOM + class changes (includes <i> class swaps)
            obs.observe(btn, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class']
            });

            // extra safety after click (some toggles are async)
            btn.addEventListener('click', () => {
                setTimeout(ensureBullhorn, 0);
                setTimeout(ensureBullhorn, 250);
                setTimeout(ensureBullhorn, 800);
            }, { passive: true });
        })();
    </script>
</body>
</html>
