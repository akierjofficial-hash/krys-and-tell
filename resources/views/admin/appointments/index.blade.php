@extends('layouts.admin')

@section('kt_live_scope', 'appointments')
@section('kt_live_interval', 15000)

@push('styles')
<style>
    /* ===== Page header (dashboard vibe) ===== */
    .ap-wrap{
        padding: 12px 0 18px;
        border-radius: 18px;
    }
    .ap-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin: 6px 0 12px;
    }
    .ap-title{
        margin:0;
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.6px;
        line-height: 1.12;
        color: var(--text);
    }
    .ap-sub{
        margin-top: 6px;
        font-size: 13px;
        color: var(--muted);
        font-weight: 800;
    }
    .ap-pill{
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
    html[data-theme="dark"] .ap-pill{
        background: rgba(96,165,250,.10);
        border-color: rgba(96,165,250,.20);
        color: #93c5fd;
        box-shadow: 0 16px 26px rgba(0,0,0,.35);
    }

    /* ===== Glass cards ===== */
    .glass{
        position:relative;
        overflow:hidden;
        border-radius: 22px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,23,42,.10);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        backdrop-filter: blur(10px);
        transition: .18s ease;
    }
    .glass::before{
        content:"";
        position:absolute;
        inset:-2px;
        background:
            radial-gradient(900px 260px at 18% 0%, rgba(37,99,235,.12), transparent 55%),
            radial-gradient(900px 260px at 82% 0%, rgba(124,58,237,.10), transparent 60%);
        opacity:.9;
        pointer-events:none;
    }
    .glass:hover{
        transform: translateY(-1px);
        box-shadow: 0 22px 44px rgba(15,23,42,.14);
    }
    html[data-theme="dark"] .glass{
        background: rgba(17,24,39,.78);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 18px 48px rgba(0,0,0,.45);
    }
    html[data-theme="dark"] .glass:hover{
        box-shadow: 0 26px 60px rgba(0,0,0,.55);
    }
    .glass-inner{ position:relative; z-index:1; }

    /* ===== Filters ===== */
    .filters{
        padding: 14px 14px;
        margin-bottom: 14px;
    }
    .f-row{
        display:flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
    }
    .f-left{
        display:flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        flex: 1;
        min-width: 260px;
    }
    .f-right{
        display:flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .kt-input, .kt-select{
        padding: 11px 12px;
        border-radius: 14px;
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.72);
        color: var(--text);
        font-weight: 900;
        outline: none;
        transition: .15s ease;
        box-shadow: 0 12px 18px rgba(15,23,42,.06);
    }
    .kt-input{
        min-width: 260px;
        flex: 1;
    }
    .kt-input:focus, .kt-select:focus{
        border-color: rgba(37,99,235,.30);
        box-shadow: 0 16px 26px rgba(37,99,235,.12);
    }

    html[data-theme="dark"] .kt-input,
    html[data-theme="dark"] .kt-select{
        background: rgba(2,6,23,.40);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 12px 22px rgba(0,0,0,.35);
        color: var(--text);
    }
    html[data-theme="dark"] .kt-select option{
        background: rgba(2,6,23,.95);
        color: var(--text);
    }

    .kt-btn{
        padding: 11px 14px;
        border-radius: 14px;
        border: 1px solid rgba(37,99,235,.22);
        background: rgba(37,99,235,.10);
        color: var(--text);
        font-weight: 950;
        cursor: pointer;
        transition: .15s ease;
        box-shadow: 0 12px 18px rgba(37,99,235,.10);
        white-space: nowrap;
    }
    .kt-btn:hover{
        transform: translateY(-1px);
        border-color: rgba(37,99,235,.35);
        box-shadow: 0 18px 28px rgba(37,99,235,.14);
    }
    .kt-btn:disabled{
        opacity: .55;
        cursor: not-allowed;
        transform:none;
        box-shadow:none;
    }

    /* ===== Table card ===== */
    .table-card{ padding: 14px 14px; }
    .table-wrap{
        overflow:auto;
        border-radius: 18px;
        border: 1px solid rgba(15,23,42,.08);
        background: rgba(255,255,255,.70);
    }
    html[data-theme="dark"] .table-wrap{
        background: rgba(2,6,23,.20);
        border-color: rgba(148,163,184,.18);
    }

    table.kt-table{
        width:100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 920px;
    }
    .kt-table th, .kt-table td{
        padding: 12px 12px;
        border-bottom: 1px solid rgba(148,163,184,.16);
        vertical-align: middle;
        white-space: nowrap;
        font-weight: 800;
    }
    .kt-table th{
        font-size: 11px;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: rgba(15,23,42,.55);
        background: rgba(248,250,252,.85);
        position: sticky;
        top: 0;
        z-index: 2;
    }
    html[data-theme="dark"] .kt-table th{
        background: rgba(17,24,39,.65);
        color: rgba(248,250,252,.68);
    }

    .kt-table tbody tr{
        transition: .15s ease;
    }
    .kt-table tbody tr:hover{
        background: rgba(37,99,235,.06);
        transform: translateY(-1px);
    }
    html[data-theme="dark"] .kt-table tbody tr:hover{
        background: rgba(96,165,250,.10);
    }

    .muted{ color: var(--muted); font-weight: 900; }

    /* Procedure pill */
    .pill{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.75);
        box-shadow: 0 10px 18px rgba(15,23,42,.06);
    }
    html[data-theme="dark"] .pill{
        background: rgba(2,6,23,.22);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 12px 22px rgba(0,0,0,.35);
    }
    .pill-dot{
        width: 8px; height: 8px; border-radius: 999px;
        box-shadow: 0 10px 18px rgba(15,23,42,.18);
    }

    /* Status pill */
    .status{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(148,163,184,.18);
        white-space: nowrap;
    }

    /* Footer */
    .footer-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        margin-top: 12px;
        flex-wrap: wrap;
        padding: 0 2px;
    }
</style>
@endpush

@section('content')
<div class="ap-wrap">

    <div class="ap-head">
        <div>
            <h2 class="ap-title">Appointments</h2>
            <div class="ap-sub">Read-only list (created from Staff side).</div>
        </div>
        <div class="ap-pill">
            <i class="fa fa-lock"></i> Read-only
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.appointments.index') }}">
        <div class="glass filters">
            <div class="glass-inner">
                <div class="f-row">
                    <div class="f-left">
                        <input
                            class="kt-input"
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Search appointment (patient or doctor)"
                        />

                        <select class="kt-select" name="doctor">
                            <option value="">All Doctors</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d }}" @selected($doctor === $d)>{{ $d }}</option>
                            @endforeach
                        </select>

                        <select class="kt-select" name="service_id">
                            <option value="all">All Procedures</option>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}" @selected((string)$serviceId === (string)$svc->id)>{{ $svc->name }}</option>
                            @endforeach
                        </select>

                        <select class="kt-select" name="status">
                            <option value="all">All Status</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" @selected($status === $st)>{{ $st }}</option>
                            @endforeach
                        </select>

                        <button class="kt-btn" type="submit">
                            <i class="fa fa-filter me-2"></i>Apply
                        </button>
                    </div>

                    <div class="f-right">
                        <button class="kt-btn" type="button" disabled title="Export will be added later">
                            <i class="fa fa-file-export me-2"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass table-card">
        <div class="glass-inner">
            <div class="table-wrap">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th style="width: 52px;">#</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Procedure</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $i => $a)
                            @php
                                $patient = trim(($a->patient?->first_name ?? '') . ' ' . ($a->patient?->last_name ?? '')) ?: 'Patient';
                                $doctorName = $a->dentist_name ?: ($a->assigned_doctor ?: '—');
                                $procName = $a->service?->name ?? '—';

                                $procColor = $a->service?->color ?: \App\Http\Controllers\Admin\AdminAppointmentController::fallbackServiceColor($a->service_id, $procName);

                                $st = strtolower((string)($a->status ?? ''));
                                $statusStyle = match(true) {
                                    str_contains($st, 'confirm') => 'background: rgba(34,197,94,.14); color: #16a34a; border-color: rgba(34,197,94,.25);',
                                    str_contains($st, 'pend') => 'background: rgba(245,158,11,.14); color: #d97706; border-color: rgba(245,158,11,.25);',
                                    str_contains($st, 'cancel') => 'background: rgba(239,68,68,.14); color: #dc2626; border-color: rgba(239,68,68,.25);',
                                    str_contains($st, 'done') || str_contains($st, 'complete') => 'background: rgba(59,130,246,.14); color: #2563eb; border-color: rgba(59,130,246,.25);',
                                    default => 'background: rgba(148,163,184,.12); color: var(--text); border-color: rgba(148,163,184,.22);',
                                };

                                $dateLabel = $a->appointment_date ? \Carbon\Carbon::parse($a->appointment_date)->format('m.d.Y') : '—';
                                $timeLabel = $a->time_label ?? '—';
                                $endTime = $a->end_time_label ? '–' . $a->end_time_label : '';
                            @endphp

                            <tr>
                                <td class="muted">{{ $appointments->firstItem() + $i }}</td>
                                <td style="font-weight: 950;">{{ $patient }}</td>
                                <td class="muted">{{ $doctorName }}</td>
                                <td>
                                    <span class="pill" style="background: color-mix(in srgb, {{ $procColor }} 14%, transparent);">
                                        <span class="pill-dot" style="background: {{ $procColor }};"></span>
                                        {{ $procName }}
                                    </span>
                                </td>
                                <td class="muted">{{ $dateLabel }}</td>
                                <td class="muted">{{ $timeLabel }} {{ $endTime }}</td>
                                <td>
                                    <span class="status" style="{{ $statusStyle }}">
                                        {{ $a->status ?: '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="muted" style="padding: 18px;">
                                    No appointments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="footer-row">
                <div class="muted">
                    Showing {{ $appointments->firstItem() ?? 0 }} to {{ $appointments->lastItem() ?? 0 }}
                    of {{ $appointments->total() }} appointments
                </div>
                <div>
                    {{ $appointments->links() }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
