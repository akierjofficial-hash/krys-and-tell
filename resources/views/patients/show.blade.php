@extends('layouts.app')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);
    }

    /* Header */
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.3px;
        margin: 0;
        color: #0f172a;
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: rgba(15, 23, 42, .55);
    }

    .btn-ghostx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid rgba(15, 23, 42, .12);
        color: rgba(15, 23, 42, .75);
        background: rgba(255,255,255,.85);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-ghostx:hover{
        background: rgba(15, 23, 42, .04);
        color: rgba(15, 23, 42, .85);
    }

    .btn-primaryx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        border: none;
        color: #fff;
        text-decoration: none;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13, 110, 253, .20);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .26);
        color:#fff;
    }

    /* Cards */
    .card-shell{
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 14px;
    }
    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .card-title{
        margin: 0;
        font-weight: 900;
        font-size: 14px;
        color: #0f172a;
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .card-hint{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }
    .card-bodyx{ padding: 18px; }

    /* Profile header inside card */
    .profile{
        display:flex;
        align-items:center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .avatar{
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display:grid;
        place-items:center;
        font-weight: 900;
        color: #0d6efd;
        background: rgba(13,110,253,.10);
        border: 1px solid rgba(13,110,253,.18);
    }
    .profile h3{
        margin: 0;
        font-weight: 900;
        letter-spacing: -0.2px;
        color:#0f172a;
        font-size: 18px;
    }
    .profile .meta{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
        margin-top: 2px;
    }

    /* Info grid */
    .info-grid{
        margin-top: 14px;
        display:grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }
    @media (min-width: 768px){
        .info-grid{ grid-template-columns: 1fr 1fr; }
    }

    .info{
        padding: 12px 12px;
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248,250,252,.8);
    }
    .info .label{
        font-size: 12px;
        font-weight: 800;
        color: rgba(15, 23, 42, .55);
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 6px;
    }
    .info .value{
        font-size: 14px;
        font-weight: 700;
        color:#0f172a;
        word-break: break-word;
    }
    .info.full{ grid-column: 1 / -1; }

    /* Chips */
    .chip{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .chip-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .chip-male{ background: rgba(59,130,246,.12); color:#1d4ed8; border-color: rgba(59,130,246,.25); }
    .chip-female{ background: rgba(236,72,153,.12); color:#be185d; border-color: rgba(236,72,153,.25); }
    .chip-other{ background: rgba(107,114,128,.12); color: rgba(15,23,42,.75); border-color: rgba(107,114,128,.25); }

    .chip-status-upcoming{ background: rgba(13,110,253,.12); color:#0d6efd; border-color: rgba(13,110,253,.25); }
    .chip-status-completed{ background: rgba(16,185,129,.14); color:#047857; border-color: rgba(16,185,129,.25); }
    .chip-status-cancelled{ background: rgba(239,68,68,.12); color:#b91c1c; border-color: rgba(239,68,68,.25); }
    .chip-status-other{ background: rgba(107,114,128,.12); color: rgba(15,23,42,.75); border-color: rgba(107,114,128,.25); }

    /* Table */
    .table-wrap{ padding: 8px 10px 10px 10px; }
    table{
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: rgba(15, 23, 42, .55);
        padding: 14px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248, 250, 252, .9);
        position: sticky;
        top: 0;
        z-index: 1;
        white-space: nowrap;
    }
    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: #0f172a;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        vertical-align: middle;
    }
    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(13,110,253,.06); }
    .muted{ color: rgba(15, 23, 42, .55); }

    /* Keep content nice on ultra-wide screens */
    .max-wrap{ max-width: 1200px; }
</style>

@php
    $fullName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
    $initials = strtoupper(substr($patient->first_name ?? 'P', 0, 1) . substr($patient->last_name ?? 'D', 0, 1));

    $gender = strtolower($patient->gender ?? '');
    $genderClass = match($gender) {
        'male' => 'chip-male',
        'female' => 'chip-female',
        default => 'chip-other',
    };
@endphp

<div class="page-head max-wrap">
    <div>
        <h2 class="page-title">Patient Details</h2>
        <p class="subtitle">View patient information and full history (visits, appointments, and payments)</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('staff.patients.index') }}" class="btn-ghostx">
            <i class="fa fa-arrow-left"></i> Back
        </a>

        <a href="{{ route('staff.patients.edit', $patient->id) }}" class="btn-primaryx">
            <i class="fa fa-pen"></i> Edit Patient
        </a>
    </div>
</div>

{{-- Patient info card --}}
<div class="card-shell max-wrap">
    <div class="card-head">
        <div class="card-title">
            <i class="fa fa-user"></i> Profile
        </div>
        <div class="card-hint">
            Patient ID: <strong>#{{ $patient->id }}</strong>
        </div>
    </div>

    <div class="card-bodyx">
        <div class="profile">
            <div class="avatar">{{ $initials }}</div>
            <div>
                <h3>{{ $fullName ?: '—' }}</h3>
                <div class="meta">
                    @if($patient->gender)
                        <span class="chip {{ $genderClass }}">
                            <span class="chip-dot"></span> {{ ucfirst($patient->gender) }}
                        </span>
                    @else
                        <span class="muted">Gender: —</span>
                    @endif

                    <span class="ms-2 muted">•</span>
                    <span class="ms-2 muted">
                        Birthdate:
                        {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('M d, Y') : '—' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info">
                <div class="label">First Name</div>
                <div class="value">{{ $patient->first_name ?? '—' }}</div>
            </div>

            <div class="info">
                <div class="label">Last Name</div>
                <div class="value">{{ $patient->last_name ?? '—' }}</div>
            </div>

            <div class="info">
                <div class="label">Middle Name</div>
                <div class="value">{{ $patient->middle_name ?? '—' }}</div>
            </div>

            <div class="info">
                <div class="label">Contact Number</div>
                <div class="value">{{ $patient->contact_number ?? '—' }}</div>
            </div>

            <div class="info full">
                <div class="label">Address</div>
                <div class="value">{{ $patient->address ?? '—' }}</div>
            </div>

            <div class="info full">
                <div class="label">Notes</div>
                <div class="value">{{ $patient->notes ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Visits card --}}
<div class="card-shell max-wrap">
    <div class="card-head">
        <div class="card-title">
            <i class="fa fa-calendar-check"></i> Visits
        </div>
        <div class="card-hint">
            Total: <strong>{{ $visits->total() }}</strong>
        </div>
    </div>

    <div class="table-wrap table-responsive">
        @if($visits->isEmpty())
            <div class="p-3 text-muted">No visits found.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Visit Date</th>
                        <th>Notes / Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visits as $visit)
                        <tr>
                            <td class="fw-semibold">
                                {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') : '—' }}
                            </td>
                            <td class="muted">
                                {{ $visit->notes ?? $visit->description ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($visits->hasPages())
        <div class="p-3">
            {{ $visits->appends(request()->except('visits_page'))->links() }}
        </div>
    @endif
</div>

{{-- Appointments history --}}
<div class="card-shell max-wrap">
    <div class="card-head">
        <div class="card-title">
            <i class="fa fa-calendar"></i> Appointments History
        </div>
        <div class="card-hint">
            Total: <strong>{{ $appointments->total() }}</strong>
        </div>
    </div>

    <div class="table-wrap table-responsive">
        @if($appointments->isEmpty())
            <div class="p-3 text-muted">No appointments found.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Service</th>
                        <th>Dentist</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appointment)
                        @php
                            $st = strtolower($appointment->status ?? '');
                            $statusClass = match($st){
                                'upcoming' => 'chip-status-upcoming',
                                'completed' => 'chip-status-completed',
                                'cancelled', 'canceled' => 'chip-status-cancelled',
                                default => 'chip-status-other',
                            };
                        @endphp
                        <tr>
                            <td class="fw-semibold">
                                {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') : '—' }}
                            </td>
                            <td class="muted">
                                {{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : '—' }}
                            </td>
                            <td class="muted">
                                {{ optional($appointment->service)->name ?? '—' }}
                            </td>
                            <td class="muted">
                                {{ $appointment->dentist_name ?? '—' }}
                            </td>
                            <td>
                                <span class="chip {{ $statusClass }}">
                                    <span class="chip-dot"></span> {{ $appointment->status ?? '—' }}
                                </span>
                            </td>
                            <td class="muted">
                                {{ $appointment->notes ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($appointments->hasPages())
        <div class="p-3">
            {{ $appointments->appends(request()->except('appointments_page'))->links() }}
        </div>
    @endif
</div>

{{-- Payments history --}}
<div class="card-shell max-wrap">
    <div class="card-head">
        <div class="card-title">
            <i class="fa fa-receipt"></i> Payments History
        </div>
        <div class="card-hint">
            Total payments: <strong>{{ $payments->total() }}</strong>
            <span class="ms-2 muted">•</span>
            Total paid: <strong>₱{{ number_format((float) $totalPaid, 2) }}</strong>
        </div>
    </div>

    <div class="table-wrap table-responsive">
        @if($payments->isEmpty())
            <div class="p-3 text-muted">No payments found.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Visit Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td class="fw-semibold">
                                {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : '—' }}
                            </td>
                            <td class="muted">
                                {{ optional($payment->visit)->visit_date ? \Carbon\Carbon::parse($payment->visit->visit_date)->format('M d, Y') : '—' }}
                            </td>
                            <td class="fw-semibold">
                                ₱{{ number_format((float) $payment->amount, 2) }}
                            </td>
                            <td class="muted">
                                {{ ucfirst($payment->method ?? '—') }}
                            </td>
                            <td class="muted">
                                {{ $payment->notes ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($payments->hasPages())
        <div class="p-3">
            {{ $payments->appends(request()->except('payments_page'))->links() }}
        </div>
    @endif
</div>

@endsection
