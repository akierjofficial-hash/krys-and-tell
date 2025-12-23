@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">
<style>
    /* ===== Header (dashboard vibe) ===== */
    .s-wrap{
        padding: 12px 0 18px;
        border-radius: 18px;
    }
    .s-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin: 6px 0 12px;
    }
    .s-title{
        margin:0;
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.6px;
        line-height: 1.12;
        color: var(--text);
    }
    .s-sub{
        margin-top: 6px;
        font-size: 13px;
        color: var(--muted);
        font-weight: 800;
    }
    .s-pill{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 9px 12px;
        border-radius: 999px;
        border: 1px solid rgba(37,99,235,.18);
        background: rgba(37,99,235,.08);
        font-weight: 900;
        font-size: 12px;
        color: #1d4ed8;
        white-space: nowrap;
        box-shadow: 0 10px 18px rgba(37,99,235,.10);
    }
    html[data-theme="dark"] .s-pill{
        background: rgba(96,165,250,.10);
        border-color: rgba(96,165,250,.20);
        color: #93c5fd;
        box-shadow: 0 16px 26px rgba(0,0,0,.35);
    }

    /* ===== Card (glass) ===== */
    .cal-shell{
        position:relative;
        overflow:hidden;
        border-radius: 22px;
        padding: 14px 14px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,23,42,.10);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        backdrop-filter: blur(10px);
        transition: .18s ease;
    }
    .cal-shell::before{
        content:"";
        position:absolute;
        inset:-2px;
        background:
            radial-gradient(900px 260px at 18% 0%, rgba(37,99,235,.12), transparent 55%),
            radial-gradient(900px 260px at 82% 0%, rgba(124,58,237,.10), transparent 60%);
        opacity:.9;
        pointer-events:none;
    }
    .cal-shell:hover{
        transform: translateY(-1px);
        box-shadow: 0 22px 44px rgba(15,23,42,.14);
    }
    html[data-theme="dark"] .cal-shell{
        background: rgba(17,24,39,.78);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 18px 48px rgba(0,0,0,.45);
    }
    html[data-theme="dark"] .cal-shell:hover{
        box-shadow: 0 26px 60px rgba(0,0,0,.55);
    }

    .cal-inner{ position:relative; z-index:1; }

    /* ===== Calendar Container ===== */
    #adminScheduleCalendar{
        border-radius: 18px;
        overflow:hidden;
        background: rgba(255,255,255,.78);
        border: 1px solid rgba(15,23,42,.08);
        box-shadow: 0 18px 34px rgba(15,23,42,.08);
    }
    html[data-theme="dark"] #adminScheduleCalendar{
        background: rgba(2,6,23,.18);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 18px 48px rgba(0,0,0,.35);
    }

    /* ===== FullCalendar skin (match dashboard) ===== */
    .fc .fc-header-toolbar{
        margin: 0 !important;
        padding: 14px 14px 12px;
        background:
            radial-gradient(900px 280px at 20% 0%, rgba(37,99,235,.10), transparent 55%),
            radial-gradient(900px 280px at 80% 0%, rgba(124,58,237,.08), transparent 55%),
            rgba(255,255,255,.88);
        border-bottom: 1px solid rgba(15,23,42,.06);
    }
    html[data-theme="dark"] .fc .fc-header-toolbar{
        background:
            radial-gradient(900px 280px at 20% 0%, rgba(96,165,250,.10), transparent 55%),
            radial-gradient(900px 280px at 80% 0%, rgba(124,58,237,.08), transparent 55%),
            rgba(17,24,39,.80);
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

    html[data-theme="dark"] .fc .fc-button{
        background: rgba(2,6,23,.45) !important;
        border-color: rgba(148,163,184,.18) !important;
        color: var(--text) !important;
        box-shadow: 0 12px 22px rgba(0,0,0,.35) !important;
    }
    html[data-theme="dark"] .fc .fc-button:hover{
        background: rgba(96,165,250,.12) !important;
        border-color: rgba(96,165,250,.30) !important;
    }

    /* Grid softer */
    .fc-theme-standard td,
    .fc-theme-standard th{
        border-color: rgba(15,23,42,.06) !important;
    }
    html[data-theme="dark"] .fc-theme-standard td,
    html[data-theme="dark"] .fc-theme-standard th{
        border-color: rgba(148,163,184,.16) !important;
    }
    .fc .fc-scrollgrid{ border: 0 !important; }

    /* Time axis */
    .fc .fc-timegrid-slot{
        height: 44px;
    }
    .fc .fc-timegrid-slot-label{
        font-size: 11px;
        font-weight: 900;
        color: rgba(15,23,42,.55);
    }
    html[data-theme="dark"] .fc .fc-timegrid-slot-label{
        color: rgba(248,250,252,.70);
    }
    .fc .fc-timegrid-axis{
        background: rgba(248,250,252,.75);
    }
    html[data-theme="dark"] .fc .fc-timegrid-axis{
        background: rgba(17,24,39,.55);
    }

    /* Today highlight */
    .fc .fc-day-today{
        background: rgba(37,99,235,.06) !important;
        position: relative;
    }
    html[data-theme="dark"] .fc .fc-day-today{
        background: rgba(96,165,250,.10) !important;
    }

    /* ===== Event pill UI ===== */
    .fc .fc-event{
        border: 0 !important;
        border-radius: 16px !important;
        box-shadow: 0 14px 26px rgba(15,23,42,.12);
        overflow: hidden;
        position: relative;
    }
    html[data-theme="dark"] .fc .fc-event{
        box-shadow: 0 14px 30px rgba(0,0,0,.45);
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
    .fc .fc-timegrid-event .fc-event-main{
        padding: 8px 10px;
    }

    .kt-evt{
        line-height: 1.12;
        display:flex;
        flex-direction:column;
        gap: 4px;
        color: #fff;
    }
    .kt-evt .t{ font-weight: 950; font-size: 12px; letter-spacing: .1px; }
    .kt-evt .s{ font-weight: 900; font-size: 11px; opacity: .95; }
    .kt-evt .d{ font-weight: 900; font-size: 11px; opacity: .92; }
    .kt-evt .m{ font-weight: 900; font-size: 11px; opacity: .92; }

    /* ===== Legend chips ===== */
    .legend-bottom{
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid rgba(148,163,184,.20);
        display:flex;
        flex-wrap:wrap;
        gap: 10px;
    }
    .legend-chip{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 8px 10px;
        border-radius: 999px;
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.70);
        font-weight: 900;
        font-size: 12px;
        color: var(--text);
        box-shadow: 0 10px 18px rgba(15,23,42,.06);
        transition: .15s ease;
    }
    .legend-chip:hover{
        transform: translateY(-1px);
        box-shadow: 0 16px 26px rgba(15,23,42,.10);
        border-color: rgba(37,99,235,.20);
    }
    html[data-theme="dark"] .legend-chip{
        background: rgba(2,6,23,.30);
        border-color: rgba(148,163,184,.18);
        color: var(--text);
        box-shadow: 0 12px 24px rgba(0,0,0,.35);
    }
    .legend-dot{
        width:10px; height:10px;
        border-radius: 999px;
        box-shadow: 0 10px 18px rgba(15,23,42,.18);
    }
</style>
@endpush

@section('content')
<div class="s-wrap">

    <div class="s-head">
        <div>
            <h2 class="s-title">Schedule</h2>
            <div class="s-sub">Read-only weekly view (appointments created from Staff side).</div>
        </div>
        <div class="s-pill">
            <i class="fa fa-lock"></i> Read-only
        </div>
    </div>

    <div class="cal-shell">
        <div class="cal-inner">
            <div id="adminScheduleCalendar"></div>

            {{-- Legend at bottom --}}
            @if(isset($services) && $services->count())
                <div class="legend-bottom">
                    @foreach($services as $svc)
                        <div class="legend-chip">
                            <span class="legend-dot" style="background: {{ $svc->display_color }};"></span>
                            <span>{{ $svc->name }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('adminScheduleCalendar');
    if (!el) return;

    const calendar = new FullCalendar.Calendar(el, {
        initialView: 'timeGridWeek',
        height: 760,
        expandRows: true,
        nowIndicator: true,
        stickyHeaderDates: true,
        firstDay: 1, // Monday

        slotMinTime: "08:00:00",
        slotMaxTime: "19:00:00",
        slotDuration: "00:30:00",

        editable: false,
        selectable: false,
        eventStartEditable: false,
        eventDurationEditable: false,

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
        },

        eventClick: function(info) {
            info.jsEvent.preventDefault();
        },

        events: "{{ route('admin.schedule.events') }}",

        displayEventTime: false,

        eventContent: function(arg) {
            const p = arg.event.extendedProps?.patient || 'Patient';
            const s = arg.event.extendedProps?.service || 'Appointment';
            const d = arg.event.extendedProps?.doctor || '—';
            const dateLabel = arg.event.extendedProps?.date_label || '';
            const timeLabel = arg.event.extendedProps?.time_label || arg.timeText || '';

            const wrap = document.createElement('div');
            wrap.className = 'kt-evt';

            const t = document.createElement('div');
            t.className = 't';
            t.textContent = p;

            const svc = document.createElement('div');
            svc.className = 's';
            svc.textContent = s;

            const doc = document.createElement('div');
            doc.className = 'd';
            doc.textContent = d;

            const meta = document.createElement('div');
            meta.className = 'm';
            meta.textContent = (dateLabel ? (dateLabel + ' • ') : '') + timeLabel;

            wrap.appendChild(t);
            wrap.appendChild(svc);
            wrap.appendChild(doc);
            wrap.appendChild(meta);

            return { domNodes: [wrap] };
        },
    });

    calendar.render();
});
</script>
@endsection
