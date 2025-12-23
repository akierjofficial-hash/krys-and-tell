@extends('layouts.app')

@section('content')

@php
    use Carbon\Carbon;

    $patientName = trim(($appointment->patient->first_name ?? '').' '.($appointment->patient->last_name ?? ''));
    $patientName = $patientName !== '' ? $patientName : 'N/A';

    $serviceName = $appointment->service->name ?? 'N/A';

    $dentistName = $appointment->dentist_name ?? 'N/A';

    $date = $appointment->appointment_date
        ? Carbon::parse($appointment->appointment_date)->format('l, M d, Y')
        : '—';

    $time = $appointment->appointment_time
        ? Carbon::parse($appointment->appointment_time)->format('h:i A')
        : '—';

    $status = strtolower((string)($appointment->status ?? 'pending'));

    $statusClass = str_contains($status,'cancel')
        ? 'st-cancelled'
        : (str_contains($status,'done') || str_contains($status,'complete')
            ? 'st-done'
            : (str_contains($status,'pend') ? 'st-pending' : 'st-default'));

    $statusText = ucfirst($appointment->status ?? 'N/A');
@endphp

<style>
    :root{
        --card-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        --card-border: 1px solid rgba(15, 23, 42, .08);
        --muted: rgba(15, 23, 42, .58);
        --text: #0f172a;
        --bg1: rgba(59,130,246,.14);
        --bg2: rgba(124,58,237,.12);
        --bg3: rgba(34,197,94,.12);
    }

    .page-wrap{
        padding: 10px 0 24px;
        background:
            radial-gradient(900px 440px at 12% -10%, var(--bg1), transparent 60%),
            radial-gradient(900px 440px at 92% 12%, var(--bg2), transparent 55%),
            radial-gradient(900px 520px at 40% 110%, var(--bg3), transparent 55%);
        border-radius: 18px;
    }

    .head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .title{
        margin:0;
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.5px;
        color: var(--text);
        line-height: 1.1;
    }
    .subtitle{
        margin:6px 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    .card{
        max-width: 900px;
        margin: 0 auto;
        background: rgba(255,255,255,.90);
        border: var(--card-border);
        box-shadow: var(--card-shadow);
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        backdrop-filter: blur(10px);
    }
    .card::before{
        content:"";
        position:absolute;
        inset:-2px;
        background:
            radial-gradient(circle at 18% 10%, rgba(37,99,235,.16), transparent 55%),
            radial-gradient(circle at 85% 30%, rgba(124,58,237,.12), transparent 60%);
        pointer-events:none;
    }

    .card-h{
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15,23,42,.06);
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap: 12px;
        position: relative;
        z-index: 1;
    }
    .card-h-left{
        display:flex;
        gap: 12px;
        align-items:center;
    }
    .icon{
        width: 52px; height: 52px;
        border-radius: 18px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        background: linear-gradient(135deg,#2563eb,#0ea5e9);
        box-shadow: 0 14px 24px rgba(37,99,235,.18);
        flex: 0 0 auto;
        font-size: 18px;
        position:relative;
        overflow:hidden;
    }
    .icon::before{
        content:"";
        position:absolute;
        inset:-40%;
        background: radial-gradient(circle at 25% 25%, rgba(255,255,255,.35), transparent 55%);
        transform: rotate(15deg);
        pointer-events:none;
    }

    .card-h h3{
        margin:0;
        font-size: 16px;
        font-weight: 900;
        color: var(--text);
        letter-spacing:-0.2px;
    }
    .card-h .meta{
        margin-top: 3px;
        font-size: 12px;
        color: var(--muted);
    }

    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid transparent;
        white-space: nowrap;
        position: relative;
        z-index: 1;
        height: fit-content;
    }
    .badge-dot{
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display:inline-block;
        background: currentColor;
    }
    .st-pending{ background: rgba(245, 158, 11, .12); color:#b45309; border-color: rgba(245,158,11,.25); }
    .st-done{ background: rgba(34, 197, 94, .12); color:#15803d; border-color: rgba(34,197,94,.25); }
    .st-cancelled{ background: rgba(239, 68, 68, .12); color:#b91c1c; border-color: rgba(239,68,68,.25); }
    .st-default{ background: rgba(59, 130, 246, .12); color:#1d4ed8; border-color: rgba(59,130,246,.25); }

    .card-b{
        padding: 16px 18px 18px;
        position: relative;
        z-index: 1;
    }

    .grid{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 768px){
        .grid{ grid-template-columns: 1fr; }
    }

    .field{
        border: 1px solid rgba(15,23,42,.08);
        background: rgba(255,255,255,.95);
        border-radius: 16px;
        padding: 12px;
    }
    .label{
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: rgba(15,23,42,.55);
        margin-bottom: 6px;
    }
    .value{
        font-size: 15px;
        font-weight: 900;
        color: var(--text);
        letter-spacing:-0.15px;
        line-height: 1.2;
    }
    .subvalue{
        margin-top: 4px;
        font-size: 12.5px;
        color: var(--muted);
    }

    .notes{
        margin-top: 12px;
    }
    .notes .value{
        font-weight: 800;
        font-size: 14px;
    }

    .actions{
        max-width: 900px;
        margin: 12px auto 0;
        display:flex;
        gap: 10px;
        justify-content:flex-end;
        flex-wrap: wrap;
    }
    .btn-soft{
        text-decoration:none;
        padding: 10px 14px;
        border-radius: 14px;
        font-weight: 900;
        border: 1px solid rgba(15,23,42,.12);
        color: rgba(15,23,42,.75);
        background: rgba(255,255,255,.92);
        transition: .15s ease;
    }
    .btn-soft:hover{ transform: translateY(-1px); box-shadow: 0 14px 26px rgba(15,23,42,.10); }
    .btn-primary-soft{
        text-decoration:none;
        padding: 10px 14px;
        border-radius: 14px;
        font-weight: 900;
        border: 1px solid rgba(37,99,235,.20);
        color: #1d4ed8;
        background: rgba(37,99,235,.08);
        transition: .15s ease;
    }
    .btn-primary-soft:hover{ transform: translateY(-1px); box-shadow: 0 14px 26px rgba(37,99,235,.12); }
</style>

<div class="page-wrap">

    <div class="head">
        <div>
            <h2 class="title">Appointment Details</h2>
            <div class="subtitle">View patient, schedule, and appointment status.</div>
        </div>

        <span class="badge-soft {{ $statusClass }}">
            <span class="badge-dot"></span>
            {{ $statusText }}
        </span>
    </div>

    <div class="card">
        <div class="card-h">
            <div class="card-h-left">
                <div class="icon"><i class="fa fa-calendar-check"></i></div>
                <div>
                    <h3>{{ $patientName }}</h3>
                    <div class="meta">{{ $date }} • {{ $time }} • {{ $serviceName }}</div>
                </div>
            </div>

            <span class="pill" style="border-radius:999px;padding:7px 12px;font-weight:900;border:1px solid rgba(15,23,42,.10);background:rgba(255,255,255,.9);color:rgba(15,23,42,.72);">
                #{{ $appointment->id }}
            </span>
        </div>

        <div class="card-b">
            <div class="grid">
                <div class="field">
                    <div class="label">Patient</div>
                    <div class="value">{{ $patientName }}</div>
                    <div class="subvalue">Patient record</div>
                </div>

                <div class="field">
                    <div class="label">Dentist</div>
                    <div class="value">{{ $dentistName }}</div>
                    <div class="subvalue">Assigned provider</div>
                </div>

                <div class="field">
                    <div class="label">Service</div>
                    <div class="value">{{ $serviceName }}</div>
                    <div class="subvalue">Treatment / procedure</div>
                </div>

                <div class="field">
                    <div class="label">Schedule</div>
                    <div class="value">{{ $date }}</div>
                    <div class="subvalue">{{ $time }}</div>
                </div>
            </div>

            <div class="field notes">
                <div class="label">Notes</div>
                <div class="value">{{ $appointment->notes ?? 'None' }}</div>
            </div>
        </div>
    </div>

    <div class="actions">
        <a href="{{ route('appointments.index') }}" class="btn-soft">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>

        <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn-primary-soft">
            <i class="fa fa-pen me-1"></i> Edit Appointment
        </a>
    </div>

</div>

@endsection
