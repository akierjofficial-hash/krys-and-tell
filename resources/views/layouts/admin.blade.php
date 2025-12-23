<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Krys & Tell — Admin</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root{
            --primary: #155AC1;
            --primary-2: #759EDB;

            --bg: #E5ECF7;
            --surface: rgba(255,255,255,.86);
            --surface-solid: #FFFFFF;

            --text: #0f172a;
            --muted: rgba(15, 23, 42, .58);

            --border: rgba(15, 23, 42, .10);

            --card-shadow: 0 14px 36px rgba(15, 23, 42, .10);
            --radius: 18px;

            /* table tokens */
            --table-head: rgba(229,236,247,.55);
            --table-row-hover: rgba(21,90,193,.06);
        }

        html[data-theme="dark"]{
            color-scheme: dark;

            --bg: #0b1220;
            --surface: rgba(17,24,39,.88);
            --surface-solid: rgba(17,24,39,.92);

            --text: #f8fafc;
            --muted: rgba(248,250,252,.72);

            --border: rgba(148,163,184,.18);
            --card-shadow: 0 18px 48px rgba(0,0,0,.55);

            --table-head: rgba(2,6,23,.75);
            --table-row-hover: rgba(148,163,184,.10);
        }

        body{
            margin:0;
            font-family: "Nunito Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* soft background like dashboard */
        .app-bg{
            min-height: 100vh;
            background:
                radial-gradient(900px 440px at 12% -10%, rgba(59,130,246,.14), transparent 60%),
                radial-gradient(900px 440px at 92% 12%, rgba(124,58,237,.10), transparent 55%),
                radial-gradient(900px 520px at 40% 110%, rgba(34,197,94,.10), transparent 55%),
                var(--bg);
        }

        .shell{ display:flex; min-height: 100vh; }

        /* ===== Sidebar ===== */
        .side{
            width: 270px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 18px 14px;
            position: sticky;
            top: 0;
            height: 100vh;
            display:flex;
            flex-direction:column;
            overflow:hidden;
            backdrop-filter: blur(10px);
        }

        .brand{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 10px;
            padding: 10px 10px 14px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 10px;
        }

        .brand-left{
            display:flex;
            align-items:center;
            gap: 10px;
            min-width: 0;
        }

        .logo{
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display:grid;
            place-items:center;
            font-weight: 950;
            letter-spacing: -.4px;
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            box-shadow: 0 14px 24px rgba(15,23,42,.18);
            flex: 0 0 auto;
        }

        .brand .name{
            font-weight: 950;
            letter-spacing: -.2px;
            margin: 0;
            line-height: 1.1;
            white-space: nowrap;
            overflow:hidden;
            text-overflow: ellipsis;
        }
        .brand .sub{
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
            white-space: nowrap;
            overflow:hidden;
            text-overflow: ellipsis;
        }

        .theme-toggle{
            width: 40px;
            height: 40px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.75);
            color: var(--text);
            display:grid;
            place-items:center;
            box-shadow: 0 10px 18px rgba(15,23,42,.10);
            cursor:pointer;
            transition: .15s ease;
            flex: 0 0 auto;
        }
        html[data-theme="dark"] .theme-toggle{ background: rgba(2,6,23,.35) !important; }
        .theme-toggle:hover{ transform: translateY(-1px); }

        .navx{
            margin-top: 12px;
            display:flex;
            flex-direction:column;
            gap: 6px;
            padding: 0 6px;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }
        .navx::-webkit-scrollbar{ width: 8px; }
        .navx::-webkit-scrollbar-thumb{ background: rgba(148,163,184,.18); border-radius: 999px; }
        .navx::-webkit-scrollbar-track{ background: transparent; }

        .navx a{
            text-decoration:none;
            display:flex;
            align-items:center;
            gap: 10px;
            padding: 11px 12px;
            border-radius: 14px;
            color: var(--text);
            font-weight: 900;
            transition: .15s ease;
            position: relative;
            overflow: hidden;
        }

        .navx a i{
            width: 18px;
            color: var(--muted);
        }

        .navx a:hover{
            background: rgba(37,99,235,.08);
            transform: translateY(-1px);
        }

        .navx a.active{
            background: linear-gradient(135deg, rgba(37,99,235,.95), rgba(124,58,237,.85));
            color: #fff;
            box-shadow: 0 14px 26px rgba(37,99,235,.18);
        }
        .navx a.active i{ color:#fff; }

        .side-footer{
            margin-top:auto;
            padding: 12px 6px 0;
            border-top: 1px solid var(--border);
        }

        .logout-btn{
            width:100%;
            border-radius: 14px;
            font-weight: 950;
            padding: 10px 12px;
        }

        /* ===== Main ===== */
        .main{
            flex: 1;
            padding: 18px 20px 26px;
            min-width: 0;
        }

        /* Card base used everywhere */
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
        html[data-theme="dark"] select{
            background: rgba(2,6,23,.65) !important;
            border-color: var(--border) !important;
            color: var(--text) !important;
        }
        html[data-theme="dark"] select option{
            background: rgba(2,6,23,.95) !important;
            color: var(--text) !important;
        }

        /* Tables dark-safe */
        .table{ color: var(--text) !important; }
        .table > :not(caption) > * > *{
            background-color: transparent !important;
            color: inherit !important;
            border-color: var(--border) !important;
        }
        .table thead th{
            background: var(--table-head) !important;
            color: var(--muted) !important;
            font-weight: 950 !important;
            border-color: var(--border) !important;
        }
        html[data-theme="dark"] .table tbody tr:hover{
            background: var(--table-row-hover) !important;
        }

        /* FullCalendar dark-safe */
        html[data-theme="dark"] .fc,
        html[data-theme="dark"] .fc .fc-scrollgrid,
        html[data-theme="dark"] .fc .fc-view-harness{
            background: transparent !important;
            color: var(--text) !important;
            border-color: var(--border) !important;
        }
        html[data-theme="dark"] .fc-theme-standard td,
        html[data-theme="dark"] .fc-theme-standard th{
            border-color: rgba(148,163,184,.18) !important;
        }
        html[data-theme="dark"] .fc .fc-toolbar-title{
            color: var(--text) !important;
        }
        html[data-theme="dark"] .fc .fc-button{
            background: rgba(17,24,39,.92) !important;
            border-color: rgba(148,163,184,.22) !important;
            color: var(--text) !important;
            border-radius: 12px !important;
            font-weight: 950 !important;
        }
        html[data-theme="dark"] .fc .fc-day-today{
            background: rgba(96,165,250,.10) !important;
        }

        /* ===== Mobile Responsive Sidebar Drawer ===== */
        .side-overlay{
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.35);
            z-index: 1999;
            opacity: 0;
            pointer-events: none;
            transition: opacity .18s ease;
        }
        .side-overlay.show{
            opacity: 1;
            pointer-events: auto;
        }

        .menu-toggle{
            display:none;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: var(--surface);
            box-shadow: 0 10px 18px rgba(15,23,42,.10);
            place-items:center;
            cursor:pointer;
        }

        @media (max-width: 900px){
            .menu-toggle{ display:grid; }
            .side{
                position: fixed;
                left: 0;
                top: 0;
                height: 100dvh;
                z-index: 2000;
                transform: translateX(-105%);
                transition: transform .18s ease;
            }
            .side.open{ transform: translateX(0); }
            .main{ padding: 14px 14px 18px; }
        }
    </style>

    @stack('styles')
</head>
<body>
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
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa fa-chart-line"></i> Dashboard
                </a>

                <a href="{{ route('admin.analytics.index') }}" class="{{ request()->is('admin/analytics*') ? 'active' : '' }}">
                    <i class="fa fa-chart-pie"></i> Analytics
                </a>

                <a href="{{ route('admin.schedule.index') }}" class="{{ request()->is('admin/schedule*') ? 'active' : '' }}">
                    <i class="fa fa-calendar-check"></i> Schedule
                </a>

                <a href="{{ route('admin.appointments.index') }}" class="{{ request()->is('admin/appointments*') ? 'active' : '' }}">
                    <i class="fa fa-calendar-days"></i> Appointments
                </a>

                <a href="{{ route('admin.patients.index') }}" class="{{ request()->is('admin/patients*') ? 'active' : '' }}">
                    <i class="fa fa-users"></i> Patients
                </a>

                <a href="{{ route('admin.doctors.index') }}" class="{{ request()->is('admin/doctors*') ? 'active' : '' }}">
    <i class="fa fa-user-doctor"></i> Doctors
</a>


                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
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
            {{-- mobile menu button --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="menu-toggle" id="menuToggle" type="button" title="Menu">
                    <i class="fa fa-bars"></i>
                </button>
                <div></div>
            </div>

            @yield('content')
        </main>
    </div>
</div>

<script>
(function () {
    const html = document.documentElement;
    const btnTheme  = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');

    function applyTheme(theme){
        html.setAttribute('data-theme', theme);
        localStorage.setItem('admin_theme', theme);

        if (icon) {
            icon.classList.remove('fa-moon', 'fa-sun');
            icon.classList.add(theme === 'dark' ? 'fa-sun' : 'fa-moon');
        }
    }

    // theme init
    const saved = localStorage.getItem('admin_theme') || 'light';
    applyTheme(saved);

    btnTheme?.addEventListener('click', function () {
        const next = (html.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
        applyTheme(next);
    });

    // sidebar drawer (mobile)
    const side = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sideOverlay');
    const btnMenu = document.getElementById('menuToggle');

    function closeSidebar(){
        side?.classList.remove('open');
        overlay?.classList.remove('show');
    }

    btnMenu?.addEventListener('click', () => {
        side?.classList.toggle('open');
        overlay?.classList.toggle('show');
    });

    overlay?.addEventListener('click', closeSidebar);

    // auto-close when resizing to desktop
    window.addEventListener('resize', () => {
        if (!window.matchMedia('(max-width: 900px)').matches) closeSidebar();
    });

})();
</script>

@stack('scripts')
</body>
</html>
