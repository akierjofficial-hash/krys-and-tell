@extends('layouts.staff')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">

<style>
    :root{
        --card-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        --card-border: 1px solid rgba(15, 23, 42, .08);
        --muted: rgba(15, 23, 42, .58);
        --text: #0f172a;

        --blue: #2563eb;
        --violet:#7c3aed;
        --green:#22c55e;
        --amber:#f59e0b;

        --bg1: rgba(59,130,246,.14);
        --bg2: rgba(124,58,237,.12);
        --bg3: rgba(34,197,94,.12);
    }

    /* Background wrapper */
    .dash-wrap{
        padding: 12px 0 22px;
        background:
            radial-gradient(900px 440px at 12% -10%, var(--bg1), transparent 60%),
            radial-gradient(900px 440px at 92% 12%, var(--bg2), transparent 55%),
            radial-gradient(900px 520px at 40% 110%, var(--bg3), transparent 55%);
        border-radius: 18px;
    }

    /* Header */
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

    /* Welcome bar */
    .welcome{
        background: rgba(255,255,255,.86);
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
    }
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
        border: 1px solid rgba(37,99,235,.18);
        background: rgba(37,99,235,.08);
        font-weight: 800;
        font-size: 12px;
        color: #1d4ed8;
        white-space: nowrap;
        box-shadow: 0 10px 18px rgba(37,99,235,.10);
    }

    /* Stats grid */
    .grid-gap{ row-gap: 14px; }

    .stat-card{
        padding: 16px 16px;
        border-radius: 20px;
        background: rgba(255,255,255,.88);
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
        box-shadow: 0 22px 44px rgba(15, 23, 42, .14);
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

    /* Universal cards */
    .kt-card{
        background: rgba(255,255,255,.90);
        border: var(--card-border);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        overflow:hidden;
        backdrop-filter: blur(10px);
    }
    .kt-card-h{
        padding: 14px 16px;
        border-bottom: 1px solid rgba(15,23,42,.06);
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
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.9);
        white-space:nowrap;
        color: rgba(15,23,42,.72);
    }

    .mini-btn{
        border: 1px solid rgba(15,23,42,.12);
        background: rgba(255,255,255,.92);
        padding: 7px 11px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 12px;
        transition: .15s ease;
    }
    .mini-btn:hover{ transform: translateY(-1px); }
    .mini-btn.active{
        background: rgba(37,99,235,.10);
        border-color: rgba(37,99,235,.25);
        color: rgb(37,99,235);
        box-shadow: 0 10px 18px rgba(37,99,235,.10);
    }

    /* List items */
    .list-item{
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 16px;
        padding: 12px;
        background: rgba(255,255,255,.95);
        transition:.15s ease;
    }
    .list-item:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 28px rgba(15,23,42,.08);
    }

    /* Calendar polish */
    /* =========================
   Calendar — Ultra Modern Skin
   ========================= */
#ktCalendar{
    border-radius: 18px;
    overflow: hidden;
    background: rgba(255,255,255,.88);
    border: 1px solid rgba(15,23,42,.08);
    box-shadow: 0 18px 34px rgba(15,23,42,.08);
}

/* toolbar area */
.fc .fc-header-toolbar{
    margin: 0 !important;
    padding: 14px 14px 12px;
    background:
        radial-gradient(900px 280px at 20% 0%, rgba(37,99,235,.10), transparent 55%),
        radial-gradient(900px 280px at 80% 0%, rgba(124,58,237,.08), transparent 55%),
        rgba(255,255,255,.90);
    border-bottom: 1px solid rgba(15,23,42,.06);
}

.fc .fc-toolbar-title{
    font-weight: 950;
    letter-spacing: -0.4px;
    color: var(--text);
    font-size: 16px;
}

/* toolbar buttons */
.fc .fc-button{
    border-radius: 14px !important;
    border: 1px solid rgba(15,23,42,.10) !important;
    background: rgba(255,255,255,.92) !important;
    color: rgba(15,23,42,.86) !important;
    font-weight: 900 !important;
    padding: 8px 12px !important;
    box-shadow: 0 12px 20px rgba(15,23,42,.06) !important;
    transition: .15s ease;
}
.fc .fc-button:hover{
    background: rgba(37,99,235,.08) !important;
    border-color: rgba(37,99,235,.20) !important;
    transform: translateY(-1px);
}
.fc .fc-button-primary:not(:disabled).fc-button-active{
    background: rgba(37,99,235,.12) !important;
    border-color: rgba(37,99,235,.25) !important;
    color: rgb(37,99,235) !important;
}

/* grid borders softer */
.fc-theme-standard td,
.fc-theme-standard th{
    border-color: rgba(15,23,42,.06) !important;
}
.fc .fc-scrollgrid{
    border: 0 !important;
}

/* day header */
.fc .fc-col-header-cell{
    background: rgba(248,250,252,.75);
}
.fc .fc-col-header-cell-cushion{
    padding: 10px 8px !important;
    font-size: 11px;
    font-weight: 950;
    letter-spacing: .35px;
    text-transform: uppercase;
    color: rgba(15,23,42,.55);
}

/* month cells */
.fc .fc-daygrid-day{
    background: rgba(255,255,255,.92);
}
.fc .fc-daygrid-day-top{
    justify-content:flex-end;
}
.fc .fc-daygrid-day-number{
    font-weight: 950;
    color: rgba(15,23,42,.78);
    background: rgba(15,23,42,.04);
    border: 1px solid rgba(15,23,42,.06);
    border-radius: 999px;
    padding: 2px 8px !important;
    margin: 8px 8px 0 0;
    font-size: 11px;
}

/* today highlight with elegant ring */
.fc .fc-day-today{
    background: rgba(37,99,235,.06) !important;
    position: relative;
}
.fc .fc-day-today::after{
    content:"";
    position:absolute;
    inset:6px;
    border-radius: 14px;
    border: 1px solid rgba(37,99,235,.18);
    pointer-events:none;
}
.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number{
    background: rgba(37,99,235,.12);
    border-color: rgba(37,99,235,.22);
    color: rgb(37,99,235);
}

/* week/day timegrid polish */
.fc .fc-timegrid-slot{
    height: 44px;
}
.fc .fc-timegrid-slot-label{
    font-size: 11px;
    font-weight: 900;
    color: rgba(15,23,42,.55);
}
.fc .fc-timegrid-axis{
    background: rgba(248,250,252,.75);
}

/* now indicator */
.fc .fc-timegrid-now-indicator-line{
    border-color: rgba(239,68,68,.70) !important;
}
.fc .fc-timegrid-now-indicator-arrow{
    border-color: rgba(239,68,68,.70) !important;
}

/* event pill style (modern) */
.fc .fc-event{
    border: 0 !important;
    border-radius: 999px !important;
    padding: 3px 8px !important;
    box-shadow: 0 12px 22px rgba(15,23,42,.10);
    overflow: hidden;
}
.fc .fc-event::before{
    content:"";
    position:absolute;
    inset:-40%;
    background: radial-gradient(circle at 20% 20%, rgba(255,255,255,.35), transparent 60%);
    transform: rotate(15deg);
    opacity:.9;
    pointer-events:none;
}
.fc .fc-event .fc-event-main{
    color: #fff !important;
    font-weight: 950;
    font-size: 11px;
    letter-spacing: .15px;
}
.fc .fc-event .fc-event-time{
    font-weight: 950;
    opacity: .95;
}

/* “more” link */
.fc .fc-daygrid-more-link{
    font-weight: 950;
    color: rgb(37,99,235);
}

/* remove heavy focus outline */
.fc .fc-button:focus,
.fc a:focus{
    box-shadow: none !important;
    outline: none !important;
}
/* ===== FullCalendar Dark Mode ===== */
html[data-theme="dark"] .fc{
  color: var(--text);
}

html[data-theme="dark"] .fc .fc-scrollgrid,
html[data-theme="dark"] .fc .fc-scrollgrid td,
html[data-theme="dark"] .fc .fc-scrollgrid th{
  border-color: var(--border) !important;
}

html[data-theme="dark"] .fc .fc-col-header-cell,
html[data-theme="dark"] .fc .fc-timegrid-axis,
html[data-theme="dark"] .fc .fc-timegrid-slot-label{
  background: rgba(17,24,39,.55) !important;
}

html[data-theme="dark"] .fc .fc-daygrid-day,
html[data-theme="dark"] .fc .fc-timegrid-col{
  background: rgba(17,24,39,.35) !important;
}

html[data-theme="dark"] .fc .fc-day-today{
  background: rgba(96,165,250,.10) !important;
}

html[data-theme="dark"] .fc .fc-button{
  background: rgba(17,24,39,.85) !important;
  border-color: var(--border) !important;
  color: var(--text) !important;
}

html[data-theme="dark"] .fc .fc-button:hover{
  background: rgba(96,165,250,.12) !important;
  border-color: rgba(96,165,250,.35) !important;
}

html[data-theme="dark"] .fc .fc-toolbar-title{
  color: var(--text) !important;
}

html[data-theme="dark"] .fc .fc-event{
  border: 1px solid rgba(255,255,255,.10) !important;
  box-shadow: 0 10px 24px rgba(0,0,0,.35);
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
        initialView: 'dayGridMonth',          // elegant default
        height: 620,
        expandRows: true,
        nowIndicator: true,
        stickyHeaderDates: true,
        firstDay: 1,                          // Monday start (PH friendly)
        dayMaxEvents: 3,                      // clean month cells

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
            // subtle hover
            info.el.style.cursor = "pointer";
            info.el.addEventListener("mouseenter", () => {
                info.el.style.transform = "translateY(-1px)";
            });
            info.el.addEventListener("mouseleave", () => {
                info.el.style.transform = "translateY(0px)";
            });
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

    // default active = month
    setActive(btnMonth);

    btnMonth?.addEventListener('click', () => { calendar.changeView('dayGridMonth'); setActive(btnMonth); });
    btnWeek?.addEventListener('click',  () => { calendar.changeView('timeGridWeek'); setActive(btnWeek); });
    btnDay?.addEventListener('click',   () => { calendar.changeView('timeGridDay'); setActive(btnDay); });
});

</script>

@endsection
