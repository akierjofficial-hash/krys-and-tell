@extends('layouts.staff')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">
<style>
.sd-page{max-width:1600px;margin:0 auto;color:var(--kt-text)}
.sd-head{display:flex;justify-content:space-between;gap:24px;align-items:flex-end;margin:2px 0 18px}
.sd-eyebrow{margin:0 0 5px;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.14em}
.sd-head h1{margin:0;font-size:clamp(26px,3vw,38px);font-weight:800;letter-spacing:-.035em}
.sd-head p{margin:5px 0 0;color:var(--kt-muted);font-size:14px}
.sd-clock{text-align:right;white-space:nowrap}.sd-clock strong{display:block;font-size:15px}.sd-clock span{font-size:13px;color:var(--kt-muted)}
.sd-actions{display:flex;gap:9px;overflow-x:auto;padding:2px 2px 12px;scrollbar-width:thin}
.sd-action{display:inline-flex;align-items:center;gap:8px;min-width:max-content;padding:10px 14px;border:1px solid var(--kt-border);border-radius:12px;background:var(--kt-surface);color:var(--kt-text);font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 5px 18px rgba(15,23,42,.05)}
.sd-action:hover{color:var(--kt-primary);border-color:rgba(13,110,253,.35);transform:translateY(-1px)}
.sd-action i{color:var(--kt-primary)}
.sd-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}
.sd-kpi{display:block;min-width:0;min-height:122px;padding:17px;border:1px solid var(--kt-border);border-radius:16px;background:var(--kt-surface);color:var(--kt-text);text-decoration:none;box-shadow:0 8px 24px rgba(15,23,42,.06);transition:.16s ease}
.sd-kpi:hover{color:var(--kt-text);border-color:rgba(13,110,253,.35);transform:translateY(-2px)}
.sd-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:10px;min-width:0}.sd-kpi-label{min-width:0;font-size:12px;font-weight:750;color:var(--kt-muted)}
.sd-kpi-icon{width:35px;height:35px;flex:0 0 35px;border-radius:11px;display:grid;place-items:center;color:#0d6efd;background:#e8f1ff}
.sd-kpi strong{display:block;max-width:100%;margin-top:12px;font-size:25px;line-height:1;font-weight:800;letter-spacing:-.03em;overflow:hidden;text-overflow:ellipsis}
.sd-kpi small{display:block;margin-top:8px;font-size:11px;color:var(--kt-muted)!important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sd-workspace{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(310px,.8fr);gap:16px;align-items:start}
.sd-panel{border:1px solid var(--kt-border);border-radius:17px;background:var(--kt-surface);box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden}
.sd-panel-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 18px;border-bottom:1px solid var(--kt-border)}
.sd-panel-head h2{margin:0;font-size:17px;font-weight:800}.sd-panel-head p{margin:3px 0 0;font-size:12px;color:var(--kt-muted)}
.sd-link{font-size:12px;font-weight:750;text-decoration:none;white-space:nowrap}
.sd-table-wrap{overflow-x:auto}.sd-table{width:100%;border-collapse:collapse;min-width:760px}
.sd-table th{padding:10px 14px;background:rgba(241,245,249,.68);font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:#64748b;text-align:left;white-space:nowrap}
.sd-table td{padding:13px 14px;border-top:1px solid var(--kt-border);font-size:12px;vertical-align:middle}
.sd-table tbody tr:first-child td{border-top:0}.sd-table tbody tr:hover{background:rgba(13,110,253,.035)}
.sd-time{font-weight:800;white-space:nowrap}.sd-person{display:flex;align-items:center;gap:10px;min-width:170px}
.sd-avatar{width:34px;height:34px;flex:0 0 34px;border-radius:50%;display:grid;place-items:center;background:#e8f1ff;color:#0d6efd;font-size:11px;font-weight:800}
.sd-name{font-weight:750;color:var(--kt-text);text-decoration:none}.sd-name:hover{color:var(--kt-primary)}.sd-sub{display:block;margin-top:2px;font-size:10px;color:var(--kt-muted)}
.sd-badge{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:capitalize;white-space:nowrap;background:#e8f1ff;color:#1d4ed8}
.sd-badge.pending{background:#fff7dc;color:#a16207}.sd-badge.walked_in{background:#f0eafe;color:#6d28d9}
.sd-next{display:inline-flex;align-items:center;gap:6px;padding:7px 9px;border:1px solid rgba(13,110,253,.28);border-radius:9px;color:#0d6efd;font-size:10px;font-weight:800;text-decoration:none;white-space:nowrap}
.sd-empty{padding:32px 18px;text-align:center}.sd-empty i{font-size:24px;color:#94a3b8}.sd-empty strong{display:block;margin:8px 0 4px;font-size:14px}.sd-empty p{margin:0 0 13px;color:var(--kt-muted);font-size:12px}
.sd-attention{scroll-margin-top:90px}.sd-att-group{padding:15px 17px;border-top:1px solid var(--kt-border)}.sd-att-group:first-of-type{border-top:0}
.sd-att-title{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}.sd-att-title h3{margin:0;font-size:12px;font-weight:800}.sd-count{min-width:22px;padding:3px 7px;border-radius:999px;background:#eef2ff;color:#4338ca;text-align:center;font-size:10px;font-weight:800}
.sd-att-item{padding:11px;border-radius:12px;background:rgba(241,245,249,.65);border:1px solid rgba(148,163,184,.18)}.sd-att-item+.sd-att-item{margin-top:8px}
.sd-att-line{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.sd-att-item strong{font-size:12px}.sd-att-meta{margin-top:4px;font-size:10px;color:var(--kt-muted);line-height:1.45}.sd-att-action{display:inline-flex;margin-top:9px;font-size:10px;font-weight:800;text-decoration:none}
.sd-overdue{display:inline-block;margin-left:5px;padding:3px 6px;border-radius:99px;background:#fee2e2;color:#b91c1c;font-size:9px;font-weight:800;text-transform:uppercase}
.sd-ok{padding:4px 0;color:var(--kt-muted);font-size:11px}.sd-money{font-weight:800;color:#b45309;white-space:nowrap}
.sd-lower{display:grid;gap:16px;margin-top:16px}.sd-upcoming{min-width:690px}.sd-calendar-wrap{padding:15px;overflow-x:auto}.sd-calendar{min-width:690px}
.sd-calendar .fc{font-size:12px}.sd-calendar .fc-toolbar-title{font-size:16px!important;font-weight:800}.sd-calendar .fc-button{font-size:11px!important;text-transform:capitalize!important}.sd-calendar .fc-scroller{overflow-y:visible!important}
.sd-calendar .fc-theme-standard td,.sd-calendar .fc-theme-standard th{border-color:var(--kt-border)}.sd-calendar .fc-theme-standard .fc-scrollgrid{border-color:var(--kt-border)}
.sd-calendar .fc-col-header-cell-cushion,.sd-calendar .fc-daygrid-day-number{color:var(--kt-text);text-decoration:none}
.sd-calendar .fc-list,.sd-calendar .fc-timegrid,.sd-calendar .fc-daygrid{background:var(--kt-surface)}
.sd-panel :focus-visible,.sd-action:focus-visible,.sd-kpi:focus-visible{outline:3px solid rgba(13,110,253,.35);outline-offset:2px}
html[data-theme=dark] .sd-kpi-icon,html[data-theme=dark] .sd-avatar{background:rgba(96,165,250,.15);color:#93c5fd}
html[data-theme=dark] .sd-table th,html[data-theme=dark] .sd-att-item{background:rgba(2,6,23,.35)}
html[data-theme=dark] .sd-badge{background:rgba(59,130,246,.18);color:#bfdbfe}
html[data-theme=dark] .sd-badge.pending{background:rgba(245,158,11,.18);color:#fde68a}
@media(max-width:1180px){.sd-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.sd-workspace{grid-template-columns:minmax(0,1fr)}.sd-attention{display:grid;grid-template-columns:repeat(3,minmax(0,1fr))}.sd-attention .sd-panel-head{grid-column:1/-1}.sd-att-group{min-width:0;border-top:1px solid var(--kt-border);border-left:1px solid var(--kt-border)}.sd-att-group:nth-child(2){border-left:0}}
@media(max-width:720px){.sd-page,.sd-page>*{min-width:0;max-width:100%}.sd-head{align-items:flex-start}.sd-clock{display:none}.sd-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;overflow:visible;padding:2px 0 14px}.sd-action{min-width:0;padding:10px 8px;justify-content:center;text-align:center}.sd-action:last-child:nth-child(odd){grid-column:1/-1}.sd-kpis{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.sd-kpi{min-height:116px;padding:14px}.sd-kpi:last-child{grid-column:1/-1}.sd-kpi strong{font-size:22px}.sd-kpi small{font-size:10px}.sd-attention{display:block}.sd-att-group{border-left:0}.sd-panel-head{align-items:flex-start}.sd-calendar-wrap{padding:10px}}
@media(max-width:420px){.sd-actions{grid-template-columns:1fr}.sd-kpis{grid-template-columns:1fr}.sd-kpi:last-child{grid-column:auto}}
</style>
@endpush

@section('content')
@php
    $dashboardReturn = route('staff.dashboard');
    $todayFilter = ['date_from' => $today, 'date_to' => $today];
    $displayUser = trim(auth()->user()->name ?? '') ?: 'Staff';
@endphp
<main class="sd-page">
    <header class="sd-head">
        <div>
            <p class="sd-eyebrow">STAFF DASHBOARD</p>
            <h1>{{ $greeting }}, {{ $displayUser }}</h1>
            <p>Here’s what needs your attention today.</p>
        </div>
        <div class="sd-clock" aria-label="Current clinic date and time">
            <strong>{{ $now->format('l, M j, Y') }}</strong>
            <span>{{ $now->format('g:i A') }} · {{ config('app.timezone') }}</span>
        </div>
    </header>

    <nav class="sd-actions" aria-label="Quick actions">
        <a class="sd-action" href="{{ route('staff.patients.create', ['return' => $dashboardReturn]) }}"><i class="fa-solid fa-user-plus"></i>Add Patient</a>
        <a class="sd-action" href="{{ route('staff.appointments.create', ['return' => $dashboardReturn]) }}"><i class="fa-regular fa-calendar-plus"></i>Book Appointment</a>
        <a class="sd-action" href="{{ route('staff.records.index', ['mode' => 'visit', 'return' => $dashboardReturn]) }}"><i class="fa-solid fa-notes-medical"></i>Record Visit</a>
        <a class="sd-action" href="{{ route('staff.payments.index', ['open_record' => 1, 'return' => $dashboardReturn]) }}"><i class="fa-solid fa-money-bill-wave"></i>Record Payment</a>
        <a class="sd-action" href="{{ route('staff.records.index', ['return' => $dashboardReturn]) }}"><i class="fa-solid fa-clock-rotate-left"></i>Enter Past Records</a>
    </nav>

    <section class="sd-kpis" aria-label="Today’s clinic summary">
        <a class="sd-kpi" href="{{ route('staff.appointments.index', $todayFilter) }}">
            <span class="sd-kpi-top"><span class="sd-kpi-label">Today’s Appointments</span><span class="sd-kpi-icon"><i class="fa-regular fa-calendar-check"></i></span></span>
            <strong data-metric="today-appointments">{{ $todayAppointments->count() }}</strong><small>Active and pending appointments</small>
        </a>
        <a class="sd-kpi" href="{{ route('staff.approvals.index') }}">
            <span class="sd-kpi-top"><span class="sd-kpi-label">Pending Requests</span><span class="sd-kpi-icon"><i class="fa-solid fa-list-check"></i></span></span>
            <strong data-metric="pending-requests">{{ $pendingCount }}</strong><small>Waiting for staff review</small>
        </a>
        <a class="sd-kpi" href="{{ route('staff.visits.index', array_merge(['view' => 'all'], $todayFilter)) }}">
            <span class="sd-kpi-top"><span class="sd-kpi-label">Visits Recorded</span><span class="sd-kpi-icon"><i class="fa-solid fa-tooth"></i></span></span>
            <strong data-metric="today-visits">{{ $todayVisitsCount }}</strong><small>Clinical visit date is today</small>
        </a>
        <a class="sd-kpi" href="{{ route('staff.payments.index', array_merge(['tab' => 'transactions'], $todayFilter)) }}">
            <span class="sd-kpi-top"><span class="sd-kpi-label">Collected Today</span><span class="sd-kpi-icon"><i class="fa-solid fa-peso-sign"></i></span></span>
            <strong data-metric="collected-today">₱{{ number_format($collectedToday, 2) }}</strong><small>All receipts dated today</small>
        </a>
        <a class="sd-kpi" href="#needs-attention">
            <span class="sd-kpi-top"><span class="sd-kpi-label">Needs Attention</span><span class="sd-kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></span></span>
            <strong data-metric="needs-attention">{{ $attentionCount }}</strong><small>{{ $attentionHelper }}</small>
        </a>
    </section>

    <div class="sd-workspace">
        <section class="sd-panel" aria-labelledby="todayScheduleTitle">
            <div class="sd-panel-head">
                <div><h2 id="todayScheduleTitle">Today’s Schedule</h2><p>{{ $now->format('F j, Y') }} · {{ $todayAppointments->count() }} scheduled</p></div>
                <a class="sd-link" href="{{ route('staff.appointments.index', $todayFilter) }}">View appointments <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            @if($todayAppointments->isEmpty())
                <div class="sd-empty"><i class="fa-regular fa-calendar"></i><strong>No appointments scheduled for today.</strong><p>Add an appointment when a patient calls or arrives.</p><a class="sd-next" href="{{ route('staff.appointments.create', ['return' => $dashboardReturn]) }}"><i class="fa-solid fa-plus"></i> Book Appointment</a></div>
            @else
                <div class="sd-table-wrap">
                    <table class="sd-table">
                        <thead><tr><th scope="col">Time</th><th scope="col">Patient</th><th scope="col">Treatment</th><th scope="col">Dentist</th><th scope="col">Status</th><th scope="col">Next action</th></tr></thead>
                        <tbody>
                        @foreach($todayAppointments as $appointment)
                            @php
                                $name = $appointment->displayPatientName();
                                $initials = collect(preg_split('/\s+/', $name))->filter()->take(2)->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('');
                                $status = strtolower($appointment->status ?? '');
                                if ($status === 'pending') {
                                    $actionLabel = 'Review Request'; $actionIcon = 'fa-list-check'; $actionUrl = route('staff.approvals.index');
                                } elseif (!$appointment->doctor_id && !$appointment->dentist_name) {
                                    $actionLabel = 'Assign Dentist'; $actionIcon = 'fa-user-doctor'; $actionUrl = route('staff.appointments.edit', ['appointment' => $appointment, 'return' => $dashboardReturn]);
                                } elseif ($appointment->patient_id) {
                                    $actionLabel = 'Record Visit'; $actionIcon = 'fa-notes-medical'; $actionUrl = route('staff.records.index', ['patient_id' => $appointment->patient_id, 'mode' => 'visit', 'return' => $dashboardReturn]);
                                } else {
                                    $actionLabel = 'View Appointment'; $actionIcon = 'fa-eye'; $actionUrl = route('staff.appointments.show', ['appointment' => $appointment, 'return' => $dashboardReturn]);
                                }
                            @endphp
                            <tr>
                                <td class="sd-time">{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') : 'Walk-in' }}</td>
                                <td><div class="sd-person"><span class="sd-avatar">{{ $initials ?: '?' }}</span><span>@if($appointment->patient_id)<a class="sd-name" href="{{ route('staff.patients.show', ['patient' => $appointment->patient_id, 'return' => $dashboardReturn]) }}">{{ $name }}</a>@else<span class="sd-name">{{ $name }}</span><span class="sd-sub">Booking not linked</span>@endif</span></div></td>
                                <td>{{ $appointment->service?->name ?: 'Treatment not set' }}</td>
                                <td>{{ $appointment->displayDentistName() }}</td>
                                <td><span class="sd-badge {{ $status }}"><i class="fa-solid fa-circle" style="font-size:5px"></i>{{ str_replace('_', ' ', $status) }}</span></td>
                                <td><a class="sd-next" href="{{ $actionUrl }}"><i class="fa-solid {{ $actionIcon }}"></i>{{ $actionLabel }}</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <aside class="sd-panel sd-attention" id="needs-attention" aria-labelledby="attentionTitle">
            <div class="sd-panel-head"><div><h2 id="attentionTitle">Needs Attention</h2><p>{{ $attentionHelper }}</p></div><span class="sd-count">{{ $attentionCount }}</span></div>
            <section class="sd-att-group">
                <div class="sd-att-title"><h3><i class="fa-regular fa-calendar-xmark"></i> Booking Requests</h3><span class="sd-count">{{ $pendingCount }}</span></div>
                @if($oldestRequest)
                    @php $requestOverdue = \Carbon\Carbon::parse($oldestRequest->appointment_date)->lt($now->startOfDay()); @endphp
                    <div class="sd-att-item">
                        <div class="sd-att-line"><strong>{{ $oldestRequest->displayPatientName() }} @if($requestOverdue)<span class="sd-overdue">Overdue</span>@endif</strong></div>
                        <div class="sd-att-meta">{{ $oldestRequest->service?->name ?: 'Treatment not set' }}<br>{{ \Carbon\Carbon::parse($oldestRequest->appointment_date)->format('M j, Y') }} · {{ $oldestRequest->appointment_time ? \Carbon\Carbon::parse($oldestRequest->appointment_time)->format('g:i A') : 'Walk-in request' }}</div>
                        <a class="sd-att-action" href="{{ route('staff.approvals.index') }}">Review request <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <a class="sd-att-action" href="{{ route('staff.approvals.index') }}">View all requests</a>
                @else <div class="sd-ok"><i class="fa-solid fa-check"></i> No pending requests</div> @endif
            </section>
            <section class="sd-att-group">
                <div class="sd-att-title"><h3><i class="fa-solid fa-wallet"></i> Patient Balances to Review</h3><span class="sd-count">{{ $balanceCount }}</span></div>
                <div class="sd-att-meta">{{ $balanceKnownCount }} calculable · ₱{{ number_format($balanceKnownTotal, 2) }} known outstanding @if($balanceIncompleteCount) · {{ $balanceIncompleteCount }} incomplete, excluded from total @endif</div>
                @forelse($balanceItems as $balance)
                    <div class="sd-att-item"><div class="sd-att-line"><div><strong>{{ $balance['patient'] }}</strong><div class="sd-att-meta">{{ $balance['label'] }}</div></div><span class="sd-money">{{ $balance['incomplete'] ? 'Incomplete' : '₱'.number_format($balance['balance'], 2) }}</span></div>
                    <a class="sd-att-action" href="{{ $balance['url'] }}">{{ $balance['incomplete'] ? 'Review source record' : 'Review patient balance' }} <i class="fa-solid fa-arrow-right"></i></a></div>
                @empty <div class="sd-ok"><i class="fa-solid fa-check"></i> No recorded balances need review</div> @endforelse
                @if($balanceCount)<a class="sd-att-action" href="{{ route('staff.payments.index') }}">View payments</a>@endif
            </section>
            <section class="sd-att-group">
                <div class="sd-att-title"><h3><i class="fa-regular fa-envelope"></i> Unread Messages</h3><span class="sd-count">{{ $unreadCount }}</span></div>
                @if($latestUnread)
                    <div class="sd-att-item"><strong>{{ $latestUnread->name }}</strong><div class="sd-att-meta">{{ \Illuminate\Support\Str::limit($latestUnread->message, 76) }}<br>{{ $latestUnread->created_at->diffForHumans() }}</div><a class="sd-att-action" href="{{ route('staff.messages.show', ['message' => $latestUnread, 'return' => $dashboardReturn]) }}">Open message <i class="fa-solid fa-arrow-right"></i></a></div>
                    <a class="sd-att-action" href="{{ route('staff.messages.index') }}">View all messages</a>
                @else <div class="sd-ok"><i class="fa-solid fa-check"></i> No unread messages</div> @endif
            </section>
        </aside>
    </div>

    <div class="sd-lower">
        <section class="sd-panel" aria-labelledby="upcomingTitle">
            <div class="sd-panel-head"><div><h2 id="upcomingTitle">Upcoming · Next 7 Days</h2><p>The next five appointments after today</p></div><a class="sd-link" href="{{ route('staff.appointments.index', ['date_from' => $now->addDay()->toDateString(), 'date_to' => $now->addDays(7)->toDateString()]) }}">View appointments <i class="fa-solid fa-arrow-right"></i></a></div>
            @if($upcomingAppointments->isEmpty()) <div class="sd-empty"><i class="fa-regular fa-calendar-check"></i><strong>No upcoming appointments.</strong><p>The next seven days are currently clear.</p></div>
            @else <div class="sd-table-wrap"><table class="sd-table sd-upcoming"><thead><tr><th scope="col">Date & time</th><th scope="col">Patient</th><th scope="col">Treatment</th><th scope="col">Dentist</th><th scope="col">Status</th></tr></thead><tbody>
                @foreach($upcomingAppointments as $appointment)<tr><td class="sd-time">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('D, M j') }}<span class="sd-sub">{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') : 'Walk-in' }}</span></td><td><a class="sd-name" href="{{ $appointment->patient_id ? route('staff.patients.show', ['patient' => $appointment->patient_id, 'return' => $dashboardReturn]) : route('staff.appointments.show', ['appointment' => $appointment, 'return' => $dashboardReturn]) }}">{{ $appointment->displayPatientName() }}</a></td><td>{{ $appointment->service?->name ?: 'Treatment not set' }}</td><td>{{ $appointment->displayDentistName() }}</td><td><span class="sd-badge {{ strtolower($appointment->status) }}">{{ str_replace('_', ' ', $appointment->status) }}</span></td></tr>@endforeach
            </tbody></table></div> @endif
        </section>

        <section class="sd-panel" aria-labelledby="calendarTitle">
            <div class="sd-panel-head"><div><h2 id="calendarTitle">Clinic Calendar</h2><p>Week view of active appointments and requests</p></div></div>
            <div class="sd-calendar-wrap"><div class="sd-calendar" id="staffDashboardCalendar" aria-label="Appointment calendar"></div></div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const element = document.getElementById('staffDashboardCalendar');
    if (!element || !window.FullCalendar) return;
    const calendar = new FullCalendar.Calendar(element, {
        initialView: window.innerWidth < 760 ? 'timeGridDay' : 'timeGridWeek',
        height: 'auto',
        contentHeight: 'auto',
        expandRows: false,
        nowIndicator: true,
        firstDay: 1,
        allDayText: 'Walk-in',
        slotMinTime: '08:00:00',
        slotMaxTime: '19:00:00',
        dayMaxEvents: 3,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'timeGridWeek,dayGridMonth,timeGridDay' },
        buttonText: { today: 'Today', week: 'Week', month: 'Month', day: 'Day' },
        events: @json(route('staff.dashboard.calendar.events')),
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
        eventDidMount(info) {
            info.el.style.cursor = 'pointer';
            info.el.setAttribute('aria-label', info.event.title);
        },
        eventClick(info) {
            const url = info.event.extendedProps?.url;
            if (url) window.location.assign(url + (url.includes('?') ? '&' : '?') + 'return=' + encodeURIComponent(@json($dashboardReturn)));
        }
    });
    calendar.render();
});
</script>
@endpush
