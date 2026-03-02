@extends('layouts.staff')

@section('content')

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@500;700;800&display=swap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">

<style>
    :root {
        --dash-accent: #1f5c7f;
        --dash-accent-soft: rgba(31, 92, 127, .12);
        --dash-warm: #b88663;
        --dash-line: rgba(15, 23, 42, .12);
        --dash-muted-2: rgba(15, 23, 42, .55);
        --dash-panel-shadow: 0 18px 34px rgba(15, 23, 42, .08);
    }

    html[data-theme="dark"] {
        --dash-line: rgba(148, 163, 184, .2);
        --dash-muted-2: rgba(248, 250, 252, .62);
        --dash-accent-soft: rgba(125, 211, 252, .12);
        --dash-panel-shadow: 0 20px 42px rgba(0, 0, 0, .35);
    }

    .staff-dash {
        padding: 8px 0 20px;
        border-radius: 20px;
        background:
            radial-gradient(680px 300px at 12% -10%, rgba(184, 134, 99, .12), transparent 60%),
            radial-gradient(760px 360px at 88% 4%, rgba(31, 92, 127, .10), transparent 62%),
            var(--kt-bg);
    }

    .dash-hero {
        border: 1px solid var(--dash-line);
        border-radius: 20px;
        background:
            linear-gradient(140deg, rgba(255, 255, 255, .72), rgba(255, 255, 255, .96)),
            var(--kt-surface);
        box-shadow: var(--dash-panel-shadow);
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
    }

    html[data-theme="dark"] .dash-hero {
        background:
            linear-gradient(150deg, rgba(15, 23, 42, .76), rgba(17, 24, 39, .92)),
            var(--kt-surface);
    }

    .dash-hero .eyebrow {
        margin: 0;
        text-transform: uppercase;
        letter-spacing: .18em;
        font-size: 11px;
        font-weight: 800;
        color: var(--dash-muted-2);
    }

    .dash-hero .hero-title {
        margin: 8px 0 0;
        font-size: clamp(28px, 3.2vw, 38px);
        line-height: 1.06;
        letter-spacing: -.02em;
        color: var(--kt-text);
        font-family: 'Fraunces', serif;
        font-weight: 700;
    }

    .dash-hero .hero-sub {
        margin: 10px 0 0;
        max-width: 560px;
        font-size: 13.5px;
        color: var(--dash-muted-2);
        font-family: 'Manrope', sans-serif;
    }

    .hero-time {
        min-width: 220px;
        border-radius: 16px;
        border: 1px solid var(--dash-line);
        background: var(--kt-surface-2);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .75);
        padding: 12px 14px;
        text-align: right;
    }

    html[data-theme="dark"] .hero-time {
        box-shadow: none;
    }

    .hero-time .label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .14em;
        color: var(--dash-muted-2);
        font-weight: 800;
    }

    .hero-time .date {
        display: block;
        margin-top: 4px;
        color: var(--kt-text);
        font-size: 14px;
        font-weight: 800;
        font-family: 'Manrope', sans-serif;
    }

    .hero-time .clock {
        display: block;
        margin-top: 2px;
        color: var(--dash-muted-2);
        font-size: 13px;
        font-family: 'Manrope', sans-serif;
    }

    .kpi-card {
        height: 100%;
        border: 1px solid var(--dash-line);
        border-radius: 18px;
        background: var(--kt-surface);
        box-shadow: var(--dash-panel-shadow);
        padding: 14px 15px;
        transition: transform .15s ease, box-shadow .2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 42px rgba(15, 23, 42, .14);
    }

    .kpi-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        color: var(--dash-muted-2);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        font-family: 'Manrope', sans-serif;
    }

    .kpi-icon {
        width: 33px;
        height: 33px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: var(--dash-accent);
        background: var(--dash-accent-soft);
        border: 1px solid rgba(31, 92, 127, .2);
    }

    .kpi-value {
        margin: 0;
        color: var(--kt-text);
        font-size: 28px;
        letter-spacing: -.03em;
        font-weight: 800;
        font-family: 'Manrope', sans-serif;
    }

    .kpi-note {
        margin-top: 2px;
        font-size: 12px;
        color: var(--dash-muted-2);
        font-family: 'Manrope', sans-serif;
    }

    .dash-panel {
        border: 1px solid var(--dash-line);
        border-radius: 18px;
        background: var(--kt-surface);
        box-shadow: var(--dash-panel-shadow);
        overflow: hidden;
    }

    .panel-head {
        padding: 13px 14px;
        border-bottom: 1px solid var(--dash-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .panel-title {
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: -.01em;
        color: var(--kt-text);
        font-family: 'Manrope', sans-serif;
    }

    .panel-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
        background: var(--kt-surface-2);
        border: 1px solid var(--dash-line);
        color: var(--dash-muted-2);
        white-space: nowrap;
        font-family: 'Manrope', sans-serif;
    }

    .panel-body {
        padding: 12px;
    }

    .panel-foot {
        padding: 0 12px 12px;
    }

    .dash-action-btn {
        border-radius: 11px;
        font-weight: 800;
        font-size: 12px;
        padding: 7px 12px;
    }

    .view-toggle {
        display: inline-flex;
        border: 1px solid var(--dash-line);
        border-radius: 11px;
        overflow: hidden;
        background: var(--kt-surface-2);
    }

    .view-btn {
        border: 0;
        background: transparent;
        color: var(--dash-muted-2);
        font-size: 12px;
        font-weight: 800;
        padding: 7px 11px;
        transition: .15s ease;
        font-family: 'Manrope', sans-serif;
    }

    .view-btn.active {
        color: #fff;
        background: var(--dash-accent);
    }

    .dash-list-stack {
        display: grid;
        gap: 8px;
    }

    .dash-list-link {
        text-decoration: none;
        color: inherit;
    }

    .dash-list-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid var(--dash-line);
        background: var(--kt-surface-2);
        border-radius: 14px;
        padding: 10px 11px;
        transition: .15s ease;
    }

    .dash-list-item:hover {
        border-color: rgba(31, 92, 127, .35);
        transform: translateY(-1px);
    }

    .dash-list-title {
        margin: 0;
        color: var(--kt-text);
        font-size: 13.2px;
        font-weight: 800;
        line-height: 1.3;
        font-family: 'Manrope', sans-serif;
    }

    .dash-list-meta {
        margin: 3px 0 0;
        color: var(--dash-muted-2);
        font-size: 12px;
        line-height: 1.35;
        font-family: 'Manrope', sans-serif;
    }

    .dash-list-empty {
        border: 1px dashed var(--dash-line);
        border-radius: 12px;
        padding: 12px;
        color: var(--dash-muted-2);
        font-size: 12.5px;
        text-align: center;
        font-family: 'Manrope', sans-serif;
    }

    .status-pill {
        flex: 0 0 auto;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-radius: 999px;
        padding: 4px 8px;
        border: 1px solid transparent;
        font-family: 'Manrope', sans-serif;
    }

    .status-default {
        color: #1f5c7f;
        background: rgba(31, 92, 127, .14);
        border-color: rgba(31, 92, 127, .3);
    }

    .status-warning {
        color: #9a6700;
        background: rgba(245, 158, 11, .18);
        border-color: rgba(245, 158, 11, .35);
    }

    .status-danger {
        color: #b42318;
        background: rgba(239, 68, 68, .16);
        border-color: rgba(239, 68, 68, .3);
    }

    .status-success {
        color: #067647;
        background: rgba(34, 197, 94, .15);
        border-color: rgba(34, 197, 94, .3);
    }

    .status-outline {
        color: var(--dash-muted-2);
        background: transparent;
        border-color: var(--dash-line);
    }

    #ktCalendar {
        border: 1px solid var(--dash-line);
        border-radius: 14px;
        overflow: hidden;
        background: var(--kt-surface);
    }

    .fc .fc-header-toolbar {
        margin: 0 !important;
        padding: 12px 12px 10px;
        border-bottom: 1px solid var(--dash-line);
        background: var(--kt-surface);
    }

    .fc .fc-toolbar-title {
        font-family: 'Fraunces', serif;
        font-weight: 700;
        color: var(--kt-text);
        font-size: 20px;
        letter-spacing: -.01em;
    }

    .fc .fc-button {
        border-radius: 10px !important;
        border: 1px solid var(--dash-line) !important;
        background: var(--kt-surface-2) !important;
        color: var(--dash-muted-2) !important;
        box-shadow: none !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        font-family: 'Manrope', sans-serif !important;
        padding: 7px 10px !important;
    }

    .fc .fc-button:hover {
        color: var(--kt-text) !important;
        border-color: rgba(31, 92, 127, .35) !important;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active {
        color: #fff !important;
        border-color: var(--dash-accent) !important;
        background: var(--dash-accent) !important;
    }

    .fc .fc-col-header-cell-cushion {
        color: var(--dash-muted-2);
        text-transform: uppercase;
        letter-spacing: .08em;
        font-size: 10.5px;
        font-weight: 800;
        padding: 10px 6px !important;
        font-family: 'Manrope', sans-serif;
    }

    .fc-theme-standard td,
    .fc-theme-standard th {
        border-color: var(--dash-line) !important;
    }

    .fc .fc-scrollgrid {
        border: 0 !important;
    }

    .fc .fc-daygrid-day-number {
        color: var(--dash-muted-2);
        font-size: 11px;
        font-weight: 800;
        margin: 6px 7px 0 0;
        font-family: 'Manrope', sans-serif;
    }

    .fc .fc-day-today {
        background: rgba(184, 134, 99, .09) !important;
    }

    .fc .fc-event {
        border-radius: 999px !important;
        border: 0 !important;
        padding: 2px 8px !important;
        box-shadow: none !important;
    }

    .fc .fc-event .fc-event-main {
        color: #fff !important;
        font-size: 11px;
        font-weight: 700;
        font-family: 'Manrope', sans-serif;
    }

    .fc .fc-button:focus,
    .fc a:focus {
        box-shadow: none !important;
        outline: none !important;
    }

    @media (max-width: 992px) {
        .dash-hero {
            padding: 16px;
        }

        .hero-time {
            width: 100%;
            text-align: left;
            min-width: 0;
        }
    }
</style>

<div class="staff-dash">
    <section class="dash-hero mb-3">
        <div>
            <p class="eyebrow">Staff Dashboard</p>
            <h2 class="hero-title">{{ $greeting }}, {{ $user->name ?? 'Staff' }}</h2>
            <p class="hero-sub">A clean snapshot of patients, visits, payments, schedules, and approvals for today.</p>
        </div>

        <div class="hero-time">
            <span class="label">Current time</span>
            <span class="date">{{ $displayDate }}</span>
            <span class="clock">{{ $displayTime }}</span>
        </div>
    </section>

    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl">
            <div class="kpi-card">
                <div class="kpi-head">
                    <span>Patients</span>
                    <span class="kpi-icon"><i class="fa-solid fa-users"></i></span>
                </div>
                <p class="kpi-value">{{ number_format($totalPatients) }}</p>
                <div class="kpi-note">Registered records</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="kpi-card">
                <div class="kpi-head">
                    <span>Today Visits</span>
                    <span class="kpi-icon"><i class="fa-solid fa-stethoscope"></i></span>
                </div>
                <p class="kpi-value">{{ number_format($todaysVisits) }}</p>
                <div class="kpi-note">Completed check-ins</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="kpi-card">
                <div class="kpi-head">
                    <span>Today Payments</span>
                    <span class="kpi-icon"><i class="fa-solid fa-receipt"></i></span>
                </div>
                <p class="kpi-value">P{{ number_format($todaysPayments, 2) }}</p>
                <div class="kpi-note">Collected amount</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="kpi-card">
                <div class="kpi-head">
                    <span>Appointments</span>
                    <span class="kpi-icon"><i class="fa-solid fa-calendar-check"></i></span>
                </div>
                <p class="kpi-value">{{ number_format($todaysAppointments->count()) }}</p>
                <div class="kpi-note">Scheduled for today</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="kpi-card">
                <div class="kpi-head">
                    <span>Services</span>
                    <span class="kpi-icon"><i class="fa-solid fa-tooth"></i></span>
                </div>
                <p class="kpi-value">{{ number_format($services) }}</p>
                <div class="kpi-note">Available offerings</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xl-8">
            <div class="dash-panel">
                <div class="panel-head">
                    <h3 class="panel-title">Calendar</h3>

                    <div class="view-toggle">
                        <button type="button" class="view-btn active" id="btnMonth">Month</button>
                        <button type="button" class="view-btn" id="btnWeek">Week</button>
                        <button type="button" class="view-btn" id="btnDay">Day</button>
                    </div>
                </div>

                <div class="panel-body">
                    <div id="ktCalendar"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dash-panel mb-3" id="ktApprovalsCard">
                <div class="panel-head">
                    <h3 class="panel-title">Approval Requests</h3>
                    <span class="panel-chip">
                        Pending
                        <span id="ktApprovalsCount">0</span>
                    </span>
                </div>

                <div class="panel-body dash-list-stack" id="ktApprovalsList">
                    <div class="dash-list-empty">Loading approval requests...</div>
                </div>

                <div class="panel-foot">
                    <a href="{{ route('staff.approvals.index') }}" class="btn btn-sm btn-outline-primary dash-action-btn">Open approvals</a>
                </div>
            </div>

            <div class="dash-panel mb-3">
                <div class="panel-head">
                    <h3 class="panel-title">Today Schedule</h3>
                    <span class="panel-chip">{{ now()->format('D, M d') }}</span>
                </div>

                <div class="panel-body dash-list-stack">
                    @forelse($todaysAppointments->take(6) as $a)
                        @php
                            $time = $a->appointment_time ? \Carbon\Carbon::parse($a->appointment_time)->format('g:i a') : '-';
                            $name = trim(($a->patient->first_name ?? '') . ' ' . ($a->patient->last_name ?? ''));
                            $svc = $a->service->name ?? 'Appointment';
                            $status = strtolower((string)($a->status ?? 'confirmed'));
                            $statusClass = str_contains($status, 'cancel') ? 'status-danger'
                                : (str_contains($status, 'pend') ? 'status-warning'
                                : (str_contains($status, 'done') || str_contains($status, 'complete') ? 'status-success' : 'status-default'));
                        @endphp

                        <div class="dash-list-item">
                            <div>
                                <p class="dash-list-title">{{ $time }} - {{ $name !== '' ? $name : 'Patient' }}</p>
                                <p class="dash-list-meta">{{ $svc }}</p>
                            </div>
                            <span class="status-pill {{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </div>
                    @empty
                        <div class="dash-list-empty">No appointments scheduled for today.</div>
                    @endforelse
                </div>
            </div>

            <div class="dash-panel mb-3">
                <div class="panel-head">
                    <h3 class="panel-title">Upcoming</h3>
                    <span class="panel-chip">Next queue</span>
                </div>

                <div class="panel-body dash-list-stack">
                    @forelse(($upcomingAppointments ?? collect())->take(5) as $u)
                        @php
                            $uDate = $u->appointment_date ? \Carbon\Carbon::parse($u->appointment_date)->format('M d, Y') : '-';
                            $uTime = $u->appointment_time ? \Carbon\Carbon::parse($u->appointment_time)->format('g:i a') : '-';
                            $uStatus = strtolower((string)($u->status ?? 'upcoming'));
                            $uName = trim(($u->patient->first_name ?? '') . ' ' . ($u->patient->last_name ?? ''));
                        @endphp

                        <a href="{{ route('staff.appointments.show', ['appointment' => $u->id]) }}" class="dash-list-link">
                            <div class="dash-list-item">
                                <div>
                                    <p class="dash-list-title">{{ $uDate }} - {{ $uTime }}</p>
                                    <p class="dash-list-meta">{{ $uName !== '' ? $uName : 'Appointment' }}</p>
                                </div>
                                <span class="status-pill status-outline">{{ ucfirst($uStatus) }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="dash-list-empty">No upcoming appointments.</div>
                    @endforelse
                </div>
            </div>

            <div class="dash-panel">
                <div class="panel-head">
                    <h3 class="panel-title">Overdue Payments</h3>
                    <span class="panel-chip">Installments</span>
                </div>

                <div class="panel-body dash-list-stack">
                    @forelse($overdueInstallments ?? [] as $o)
                        <div class="dash-list-item">
                            <div>
                                <p class="dash-list-title">{{ $o['patient'] ?? 'Patient' }}</p>
                                <p class="dash-list-meta">{{ $o['service'] ?? 'Installment' }} - Due {{ $o['due_date'] ?? '-' }}</p>
                                <p class="dash-list-meta">P{{ number_format($o['amount'] ?? 0, 2) }}</p>
                            </div>
                            <div class="d-flex flex-column gap-1 align-items-end">
                                <a href="{{ $o['url'] ?? '#' }}" class="btn btn-sm btn-outline-primary dash-action-btn">View</a>
                                <a href="{{ route('staff.payments.index', ['tab' => 'installment']) }}" class="btn btn-sm btn-primary dash-action-btn">Payments</a>
                            </div>
                        </div>
                    @empty
                        <div class="dash-list-empty">No overdue installments.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calEl = document.getElementById('ktCalendar');
    if (!calEl) return;

    const calendar = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        height: 620,
        expandRows: true,
        nowIndicator: true,
        stickyHeaderDates: true,
        firstDay: 1,
        dayMaxEvents: 3,

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },

        slotMinTime: "08:00:00",
        slotMaxTime: "19:00:00",
        events: "{{ route('staff.dashboard.calendar.events') }}",
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },

        eventDidMount: function(info){
            info.el.style.cursor = "pointer";
        },

        eventClick: function(info){
            const url = info.event.extendedProps?.url;
            if (url) window.location.href = url;
        }
    });

    calendar.render();

    const btnMonth = document.getElementById('btnMonth');
    const btnWeek = document.getElementById('btnWeek');
    const btnDay = document.getElementById('btnDay');

    const setActive = (btn) => {
        [btnMonth, btnWeek, btnDay].forEach(b => b?.classList.remove('active'));
        btn?.classList.add('active');
    };

    setActive(btnMonth);

    btnMonth?.addEventListener('click', () => { calendar.changeView('dayGridMonth'); setActive(btnMonth); });
    btnWeek?.addEventListener('click', () => { calendar.changeView('timeGridWeek'); setActive(btnWeek); });
    btnDay?.addEventListener('click', () => { calendar.changeView('timeGridDay'); setActive(btnDay); });
});
</script>

<script>
(function(){
    const url = @json(route('staff.approvals.widget'));
    const card = document.getElementById('ktApprovalsCard');
    const countEl = document.getElementById('ktApprovalsCount');
    const listEl = document.getElementById('ktApprovalsList');
    if (!card || !countEl || !listEl) return;

    const esc = (s) => String(s ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    let lastCount = null;
    let loading = false;

    function render(items){
        if (!items.length) {
            listEl.innerHTML = '<div class="dash-list-empty">No pending requests.</div>';
            return;
        }

        listEl.innerHTML = items.map(i => `
            <div class="dash-list-item">
                <div>
                    <p class="dash-list-title">${esc(i.patient)}</p>
                    <p class="dash-list-meta">${esc(i.service)} - ${esc(i.date)} ${esc(i.time)}</p>
                    <p class="dash-list-meta">Doctor: ${esc(i.doctor)}</p>
                </div>
            </div>
        `).join('');
    }

    async function refresh(){
        if (loading || document.hidden) return;

        loading = true;
        try {
            const res = await fetch(url + '?limit=6', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            });

            if (!res.ok) throw new Error('fetch failed');

            const data = await res.json();
            const c = parseInt(data.pendingCount || 0, 10);
            countEl.textContent = c;

            const items = Array.isArray(data.items) ? data.items : [];
            render(items);

            if (lastCount !== null && c > lastCount) {
                card.style.transition = 'box-shadow .2s ease';
                card.style.boxShadow = '0 0 0 4px rgba(34, 197, 94, .16)';
                setTimeout(() => card.style.boxShadow = '', 650);
            }
            lastCount = c;
        } catch (e) {
            console.warn(e);
        } finally {
            loading = false;
        }
    }

    refresh();
    setInterval(refresh, 5000);
})();
</script>

@endsection
