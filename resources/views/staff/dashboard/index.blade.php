@extends('layouts.staff')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">

<style>
    /* (YOUR EXISTING CSS - unchanged) */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);
        --muted: var(--kt-muted);
        --text: var(--kt-text);

        --blue: #2563eb;
        --violet:#7c3aed;
        --green:#22c55e;
        --amber:#f59e0b;

        --bg1: rgba(59,130,246,.14);
        --bg2: rgba(124,58,237,.12);
        --bg3: rgba(34,197,94,.12);
    }

    html[data-theme="dark"]{
        --bg1: rgba(96,165,250,.08);
        --bg2: rgba(167,139,250,.07);
        --bg3: rgba(34,197,94,.06);
    }

    .dash-wrap{
        padding: 12px 0 22px;
        background:
            radial-gradient(900px 440px at 12% -10%, var(--bg1), transparent 60%),
            radial-gradient(900px 440px at 92% 12%, var(--bg2), transparent 55%),
            radial-gradient(900px 520px at 40% 110%, var(--bg3), transparent 55%);
        border-radius: 18px;
    }

    .dash-header{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:16px;
        margin-bottom: 14px;
        padding: 0 2px;
    }
    .dash-title{
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -0.6px;
        margin: 0;
        color: var(--text);
        line-height: 1.12;
    }
    .dash-subtitle{
        margin-top: 6px;
        font-size: 13px;
        color: var(--muted);
    }

    .welcome{
        background: var(--kt-surface);
        border: var(--card-border);
        box-shadow: var(--card-shadow);
        border-radius: 20px;
        padding: 18px 18px;
        overflow:hidden;
        position:relative;
        backdrop-filter: blur(8px);
    }
    .welcome::before{
        content:"";
        position:absolute;
        inset:-2px;
        background:
            radial-gradient(circle at 18% 10%, rgba(37,99,235,.18), transparent 55%),
            radial-gradient(circle at 85% 30%, rgba(124,58,237,.14), transparent 60%);
        pointer-events:none;
        opacity: .9;
    }
    html[data-theme="dark"] .welcome::before{ opacity: .55; }

    .welcome-inner{
        position:relative;
        z-index:1;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:14px;
        flex-wrap: wrap;
    }
    .welcome h3{
        margin: 0;
        font-size: 18px;
        font-weight: 900;
        color: var(--text);
        letter-spacing: -0.25px;
    }
    .welcome p{
        margin: 4px 0 0;
        font-size: 12.6px;
        color: var(--muted);
    }

    .welcome-badge{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 9px 12px;
        border-radius: 999px;
        border: 1px solid rgba(96,165,250,.25);
        background: var(--kt-primary-soft);
        font-weight: 800;
        font-size: 12px;
        color: var(--kt-primary);
        white-space: nowrap;
        box-shadow: 0 10px 18px rgba(37,99,235,.10);
    }

    .grid-gap{ row-gap: 14px; }

    .stat-card{
        padding: 16px 16px;
        border-radius: 20px;
        background: var(--kt-surface);
        border: var(--card-border);
        box-shadow: var(--card-shadow);
        display:flex;
        align-items:center;
        gap:14px;
        transition: .18s ease;
        position:relative;
        overflow:hidden;
        height:100%;
        backdrop-filter: blur(8px);
        color: var(--text);
    }
    .stat-card::after{
        content:"";
        position:absolute;
        inset:-2px;
        background: radial-gradient(circle at top left, rgba(37,99,235,.14), transparent 55%);
        opacity: 0;
        transition: .18s ease;
        pointer-events:none;
    }
    .stat-card:hover{
        transform: translateY(-2px);
        box-shadow: 0 22px 44px rgba(0,0,0,.25);
    }
    .stat-card:hover::after{ opacity: 1; }

    .stat-icon{
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size: 20px;
        color: #fff;
        flex:0 0 auto;
        box-shadow: 0 14px 24px rgba(15,23,42,.18);
        position:relative;
        overflow:hidden;
    }
    .stat-icon::before{
        content:"";
        position:absolute;
        inset: -40%;
        background: radial-gradient(circle at 20% 20%, rgba(255,255,255,.35), transparent 55%);
        transform: rotate(15deg);
        opacity:.9;
        pointer-events:none;
    }
    .stat-meta small{
        display:block;
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 4px;
        font-weight: 700;
    }
    .stat-meta h4{
        margin: 0;
        font-weight: 900;
        letter-spacing: -0.35px;
        color: var(--text);
        font-size: 22px;
    }

    .kt-card{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        overflow:hidden;
        backdrop-filter: blur(10px);
        color: var(--text);
    }
    .kt-card-h{
        padding: 14px 16px;
        border-bottom: 1px solid rgba(148,163,184,.16);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
    }
    .kt-card-title{
        font-weight: 900;
        color: var(--text);
        letter-spacing: -0.2px;
    }
    .kt-card-b{ padding: 14px 16px; }

    .pill{
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        white-space:nowrap;
        color: var(--muted);
    }

    .mini-btn{
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        color: var(--text);
        padding: 7px 11px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 12px;
        transition: .15s ease;
    }
    .mini-btn:hover{ transform: translateY(-1px); }
    .mini-btn.active{
        background: var(--kt-primary-soft);
        border-color: rgba(96,165,250,.35);
        color: var(--kt-primary);
        box-shadow: 0 10px 18px rgba(37,99,235,.10);
    }

    .list-item{
        border: 1px solid var(--kt-border);
        border-radius: 16px;
        padding: 12px;
        background: var(--kt-surface-2);
        transition:.15s ease;
        color: var(--text);
    }
    .list-item:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 28px rgba(0,0,0,.18);
    }

    /* FullCalendar CSS unchanged... */
    #ktCalendar{
        border-radius: 18px;
        overflow: hidden;
        background: var(--kt-surface);
        border: 1px solid var(--kt-border);
        box-shadow: 0 18px 34px rgba(0,0,0,.12);
    }
    .fc .fc-header-toolbar{
        margin: 0 !important;
        padding: 14px 14px 12px;
        background:
            radial-gradient(900px 280px at 20% 0%, rgba(37,99,235,.10), transparent 55%),
            radial-gradient(900px 280px at 80% 0%, rgba(124,58,237,.08), transparent 55%),
            var(--kt-surface);
        border-bottom: 1px solid rgba(148,163,184,.16);
    }
    .fc .fc-toolbar-title{
        font-weight: 950;
        letter-spacing: -0.4px;
        color: var(--text);
        font-size: 16px;
    }
    .fc .fc-button{
        border-radius: 14px !important;
        border: 1px solid var(--kt-border) !important;
        background: var(--kt-surface-2) !important;
        color: var(--text) !important;
        font-weight: 900 !important;
        padding: 8px 12px !important;
        box-shadow: 0 12px 20px rgba(0,0,0,.10) !important;
        transition: .15s ease;
    }
    .fc .fc-button:hover{
        background: rgba(96,165,250,.10) !important;
        border-color: rgba(96,165,250,.35) !important;
        transform: translateY(-1px);
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active{
        background: rgba(96,165,250,.14) !important;
        border-color: rgba(96,165,250,.35) !important;
        color: var(--kt-primary) !important;
    }
    .fc-theme-standard td,
    .fc-theme-standard th{ border-color: rgba(148,163,184,.16) !important; }
    .fc .fc-scrollgrid{ border: 0 !important; }
    .fc .fc-col-header-cell{ background: rgba(248,250,252,.35); }
    html[data-theme="dark"] .fc .fc-col-header-cell{ background: rgba(2,6,23,.45) !important; }
    .fc .fc-col-header-cell-cushion{
        padding: 10px 8px !important;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: var(--muted);
    }
    .fc .fc-daygrid-day{ background: var(--kt-surface); }
    html[data-theme="dark"] .fc .fc-daygrid-day{ background: rgba(17,24,39,.40) !important; }
    .fc .fc-daygrid-day-number{
        font-weight: 950;
        color: var(--muted);
        background: rgba(148,163,184,.10);
        border: 1px solid rgba(148,163,184,.16);
        border-radius: 999px;
        padding: 2px 8px !important;
        margin: 8px 8px 0 0;
        font-size: 11px;
    }
    .fc .fc-day-today{
        background: rgba(96,165,250,.08) !important;
        position: relative;
    }
    .fc .fc-day-today::after{
        content:"";
        position:absolute;
        inset:6px;
        border-radius: 14px;
        border: 1px solid rgba(96,165,250,.22);
        pointer-events:none;
    }
    .fc .fc-event{
        border: 0 !important;
        border-radius: 999px !important;
        padding: 3px 8px !important;
        box-shadow: 0 12px 22px rgba(0,0,0,.20);
        overflow: hidden;
    }
    html[data-theme="dark"] .fc .fc-event{
        border: 1px solid rgba(255,255,255,.10) !important;
        box-shadow: 0 10px 24px rgba(0,0,0,.35);
    }
    .fc .fc-event .fc-event-main{
        color: #fff !important;
        font-weight: 950;
        font-size: 11px;
        letter-spacing: .15px;
    }
    .fc .fc-daygrid-more-link{
        font-weight: 950;
        color: var(--kt-primary);
    }
    .fc .fc-button:focus,
    .fc a:focus{
        box-shadow: none !important;
        outline: none !important;
    }
</style>

<div class="dash-wrap">

    <div class="dash-header">
        <div>
            <h2 class="dash-title">Dashboard</h2>
            <div class="dash-subtitle">Overview of today’s clinic activity</div>
        </div>
    </div>

    <div class="welcome mb-3">
        <div class="welcome-inner">
            <div>
                <h3>{{ $greeting }}, {{ $user->name ?? 'Staff' }} 👋</h3>
                <p>Welcome back — here’s a quick snapshot of what’s happening today.</p>
            </div>
            <div class="welcome-badge">
                <i class="fa fa-clock"></i>
                {{ $displayDate }} • {{ $displayTime }}
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row grid-gap">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#1e90ff,#2563eb);">
                    <i class="fa fa-users"></i>
                </div>
                <div class="stat-meta">
                    <small>Patients</small>
                    <h4>{{ $totalPatients }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#7c3aed,#6f42c1);">
                    <i class="fa fa-user-check"></i>
                </div>
                <div class="stat-meta">
                    <small>Today's Visits</small>
                    <h4>{{ $todaysVisits }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#0ea5e9,#2563eb);">
                    <i class="fa fa-receipt"></i>
                </div>
                <div class="stat-meta">
                    <small>Today's Payment</small>
                    <h4>₱{{ number_format($todaysPayments, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#f1a208);">
                    <i class="fa fa-calendar-days"></i>
                </div>
                <div class="stat-meta">
                    <small>Today's Appointments</small>
                    <h4>{{ $todaysAppointments->count() }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#22c55e,#17c964);">
                    <i class="fa fa-wrench"></i>
                </div>
                <div class="stat-meta">
                    <small>Services</small>
                    <h4>{{ $services }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Calendar + Side Panels --}}
    <div class="row g-3 mt-2">
        <div class="col-lg-8">
            <div class="kt-card">
                <div class="kt-card-h">
                    <div class="kt-card-title">Calendar Activities</div>
                    <div class="d-flex gap-2">
                        <button class="mini-btn active" id="btnMonth">Month</button>
                        <button class="mini-btn" id="btnWeek">Week</button>
                        <button class="mini-btn" id="btnDay">Day</button>
                    </div>
                </div>
                <div class="kt-card-b">
                    <div id="ktCalendar"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

            {{-- ✅ LIVE APPROVAL REQUESTS CARD (NEW) --}}
            <div class="kt-card mb-3" id="ktApprovalsCard">
                <div class="kt-card-h">
                    <div class="kt-card-title">Approval Requests</div>
                    <span class="pill">
                        Pending: <span id="ktApprovalsCount">0</span>
                    </span>
                </div>
                <div class="kt-card-b d-grid gap-2" id="ktApprovalsList">
                    <div class="list-item" style="opacity:.8;">Loading…</div>
                </div>
                <div class="px-3 pb-3">
                    <a href="{{ route('staff.approvals.index') }}"
                       class="btn btn-sm btn-outline-primary"
                       style="border-radius:12px;font-weight:900;">
                        Open approvals
                    </a>
                </div>
            </div>

            <div class="kt-card mb-3">
                <div class="kt-card-h">
                    <div class="kt-card-title">Today’s Schedule</div>
                    <span class="pill">{{ now()->format('D, M d') }}</span>
                </div>

                <div class="kt-card-b d-grid gap-2">
                    @forelse($todaysAppointments->take(6) as $a)
                        @php
                            $time = $a->appointment_time ? \Carbon\Carbon::parse($a->appointment_time)->format('g:i a') : '—';
                            $name = trim(($a->patient->first_name ?? '').' '.($a->patient->last_name ?? ''));
                            $svc  = $a->service->name ?? 'Appointment';
                            $status = strtolower((string)($a->status ?? 'confirmed'));
                            $badgeClass = str_contains($status,'cancel') ? 'bg-danger'
                                : (str_contains($status,'pend') ? 'bg-warning text-dark'
                                : (str_contains($status,'done') || str_contains($status,'complete') ? 'bg-success' : 'bg-primary'));
                        @endphp

                        <div class="list-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-weight:900; color:var(--text);">{{ $time }} • {{ $name ?: 'Patient' }}</div>
                                    <div style="opacity:.8;">{{ $svc }}</div>
                                </div>
                                <span class="badge {{ $badgeClass }}" style="border-radius:999px;font-weight:900;">
                                    {{ ucfirst($status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="list-item" style="opacity:.8;">No appointments today.</div>
                    @endforelse
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-h">
                    <div class="kt-card-title">Overdue Payments</div>
                    <span class="pill">Installments</span>
                </div>

                <div class="kt-card-b d-grid gap-2">
                    @forelse($overdueInstallments ?? [] as $o)
                        <div class="list-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div style="font-weight:900;">{{ $o['patient'] ?? 'Patient' }}</div>
                                    <div style="opacity:.8;">
                                        {{ $o['service'] ?? 'Installment' }} • Due: {{ $o['due_date'] ?? '—' }}
                                    </div>
                                </div>
                                <div style="font-weight:900;">
                                    ₱{{ number_format($o['amount'] ?? 0, 2) }}
                                </div>
                            </div>

                            <div class="mt-2 d-flex gap-2">
                                <a href="{{ $o['url'] ?? '#' }}" class="btn btn-sm btn-outline-primary" style="border-radius:12px;font-weight:900;">View</a>
                                <a href="{{ route('staff.payments.index', ['tab' => 'installment']) }}" class="btn btn-sm btn-primary" style="border-radius:12px;font-weight:900;">Go to Payments</a>
                            </div>
                        </div>
                    @empty
                        <div class="list-item" style="opacity:.8;">No overdue installments 🎉</div>
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
            info.el.addEventListener("mouseenter", () => { info.el.style.transform = "translateY(-1px)"; });
            info.el.addEventListener("mouseleave", () => { info.el.style.transform = "translateY(0px)"; });
        },

        eventClick: function(info){
            const url = info.event.extendedProps?.url;
            if (url) window.location.href = url;
        }
    });

    calendar.render();

    const btnMonth = document.getElementById('btnMonth');
    const btnWeek  = document.getElementById('btnWeek');
    const btnDay   = document.getElementById('btnDay');

    const setActive = (btn) => {
        [btnMonth, btnWeek, btnDay].forEach(b => b?.classList.remove('active'));
        btn?.classList.add('active');
    };

    setActive(btnMonth);

    btnMonth?.addEventListener('click', () => { calendar.changeView('dayGridMonth'); setActive(btnMonth); });
    btnWeek?.addEventListener('click',  () => { calendar.changeView('timeGridWeek'); setActive(btnWeek); });
    btnDay?.addEventListener('click',   () => { calendar.changeView('timeGridDay'); setActive(btnDay); });
});
</script>

{{-- ✅ LIVE APPROVALS POLLING SCRIPT (NEW) --}}
<script>
(function(){
    const url = @json(route('staff.approvals.widget'));
    const card = document.getElementById('ktApprovalsCard');
    const countEl = document.getElementById('ktApprovalsCount');
    const listEl = document.getElementById('ktApprovalsList');
    if(!card || !countEl || !listEl) return;

    const esc = (s) => String(s ?? '')
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'",'&#039;');

    let lastCount = null;
    let loading = false;

    function render(items){
        if (!items.length) {
            listEl.innerHTML = `<div class="list-item" style="opacity:.8;">No pending requests 🎉</div>`;
            return;
        }

        listEl.innerHTML = items.map(i => `
            <div class="list-item">
                <div style="font-weight:900;">${esc(i.patient)}</div>
                <div style="opacity:.8;font-size:12.5px;">
                    ${esc(i.service)} • ${esc(i.date)} ${esc(i.time)}<br>
                    Doctor: ${esc(i.doctor)}
                </div>
            </div>
        `).join('');
    }

    async function refresh(){
        if (loading) return;
        if (document.hidden) return;

        loading = true;
        try{
            const res = await fetch(url + '?limit=6', {
                headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
                cache: 'no-store'
            });
            if(!res.ok) throw new Error('fetch failed');

            const data = await res.json();
            const c = parseInt(data.pendingCount || 0, 10);
            countEl.textContent = c;

            const items = Array.isArray(data.items) ? data.items : [];
            render(items);

            if (lastCount !== null && c > lastCount) {
                card.style.transition = 'box-shadow .2s ease';
                card.style.boxShadow = '0 0 0 4px rgba(34,197,94,.18)';
                setTimeout(() => card.style.boxShadow = '', 650);
            }
            lastCount = c;
        }catch(e){
            console.warn(e);
        }finally{
            loading = false;
        }
    }

    refresh();
    setInterval(refresh, 5000);
})();
</script>

@endsection
