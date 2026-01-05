@extends('layouts.staff')

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

    $info = $patient->informationRecord;
    $consent = $patient->informedConsent;

    $consentLabels = [
        'treatment'   => 'Treatment to be done',
        'meds'        => 'Drugs & medications',
        'changes'     => 'Changes in treatment plan',
        'radiograph'  => 'Radiographs / X-rays',
        'removal'     => 'Removal of teeth',
        'crowns'      => 'Crowns / caps / bridges',
        'endo'        => 'Endodontics / root canal',
        'perio'       => 'Periodontal disease',
        'fillings'    => 'Fillings',
        'dentures'    => 'Dentures',
        'disclaimer'  => 'Acknowledgement / disclaimer',
    ];
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

    {{-- ✅ PRINT PATIENT INFORMATION PDF --}}
    <a href="{{ route('staff.patients.printInfo', $patient->id) }}" target="_blank" class="btn-ghostx">
        <i class="fa fa-print"></i> Print Patient Info (PDF)
    </a>

    <a href="{{ route('staff.patients.edit', $patient->id) }}" class="btn-primaryx">
        <i class="fa fa-pen"></i> Edit Patient
    </a>
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

            {{-- ✅ Email --}}
            <div class="info">
                <div class="label">Email</div>
                <div class="value">{{ $patient->email ?? '—' }}</div>
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

{{-- =======================
    Patient Information Record
======================= --}}
<div class="card-shell max-wrap">
    <div class="card-head">
        <div class="card-title">
            <i class="fa fa-clipboard-list"></i> Patient Information Record
        </div>
        <div class="card-hint">
            @if(optional($info)->signed_at)
                Signed: <strong>{{ \Carbon\Carbon::parse($info->signed_at)->format('M d, Y h:i A') }}</strong>
            @else
                <span class="muted">Not signed</span>
            @endif
        </div>
    </div>

    <div class="card-bodyx">
        @if(!$info)
            <div class="text-muted">No Patient Information Record saved yet.</div>
        @else
            <div class="info-grid">
                <div class="info">
                    <div class="label">Nickname</div>
                    <div class="value">{{ $info->nickname ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Occupation</div>
                    <div class="value">{{ $info->occupation ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Dental Insurance</div>
                    <div class="value">{{ $info->dental_insurance ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Effective Date</div>
                    <div class="value">{{ $info->effective_date ? \Carbon\Carbon::parse($info->effective_date)->format('M d, Y') : '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Home No.</div>
                    <div class="value">{{ $info->home_no ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Office No.</div>
                    <div class="value">{{ $info->office_no ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Fax No.</div>
                    <div class="value">{{ $info->fax_no ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Minor?</div>
                    <div class="value">{{ $info->is_minor ? 'Yes' : 'No' }}</div>
                </div>

                <div class="info">
                    <div class="label">Guardian Name</div>
                    <div class="value">{{ $info->guardian_name ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Guardian Occupation</div>
                    <div class="value">{{ $info->guardian_occupation ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Referral Source</div>
                    <div class="value">{{ $info->referral_source ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Consultation Reason</div>
                    <div class="value">{{ $info->consultation_reason ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Previous Dentist</div>
                    <div class="value">{{ $info->previous_dentist ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Last Dental Visit</div>
                    <div class="value">{{ $info->last_dental_visit ? \Carbon\Carbon::parse($info->last_dental_visit)->format('M d, Y') : '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Physician Name</div>
                    <div class="value">{{ $info->physician_name ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Physician Specialty</div>
                    <div class="value">{{ $info->physician_specialty ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Blood Type</div>
                    <div class="value">{{ $info->blood_type ?? '—' }}</div>
                </div>

                <div class="info">
                    <div class="label">Blood Pressure</div>
                    <div class="value">{{ $info->blood_pressure ?? '—' }}</div>
                </div>

                <div class="info full">
                    <div class="label">Allergies</div>
                    <div class="value">
                        @php $all = $info->allergies ?? []; @endphp
                        @if(!empty($all))
                            {{ implode(', ', $all) }}
                        @else
                            —
                        @endif

                        @if($info->allergies_other)
                            <div class="muted mt-1">Other: {{ $info->allergies_other }}</div>
                        @endif
                    </div>
                </div>

                <div class="info full">
                    <div class="label">Medical Conditions</div>
                    <div class="value">
                        @php $conds = $info->medical_conditions ?? []; @endphp
                        @if(!empty($conds))
                            {{ implode(', ', $conds) }}
                        @else
                            —
                        @endif

                        @if($info->medical_conditions_other)
                            <div class="muted mt-1">Other: {{ $info->medical_conditions_other }}</div>
                        @endif
                    </div>
                </div>

                <div class="info full">
                    <div class="label">Signature</div>
                    <div class="value">
                        @if($info->signature_path)
                            <img
                                src="{{ asset('storage/'.$info->signature_path) }}"
                                alt="Patient signature"
                                style="max-width:520px;width:100%;border:1px solid rgba(15,23,42,.10);border-radius:12px;background:#fff;padding:8px;"
                            >
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- =======================
    Informed Consent
======================= --}}
<div class="card-shell max-wrap">
    <div class="card-head">
        <div class="card-title">
            <i class="fa fa-file-signature"></i> Informed Consent
        </div>
        <div class="card-hint">
            @if(optional($consent)->patient_signed_at)
                Patient Signed: <strong>{{ \Carbon\Carbon::parse($consent->patient_signed_at)->format('M d, Y h:i A') }}</strong>
            @else
                <span class="muted">Not signed</span>
            @endif
        </div>
    </div>

    <div class="card-bodyx">
        @if(!$consent)
            <div class="text-muted">No Informed Consent saved yet.</div>
        @else
            <div class="table-wrap table-responsive" style="padding:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Initials</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consentLabels as $key => $label)
                            <tr>
                                <td class="fw-semibold">{{ $label }}</td>
                                <td class="muted">{{ $consent->initials[$key] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="info-grid mt-3">
                <div class="info full">
                    <div class="label">Patient/Guardian Signature</div>
                    <div class="value">
                        @if($consent->patient_signature_path)
                            <img
                                src="{{ asset('storage/'.$consent->patient_signature_path) }}"
                                alt="Consent patient signature"
                                style="max-width:520px;width:100%;border:1px solid rgba(15,23,42,.10);border-radius:12px;background:#fff;padding:8px;"
                            >
                        @else
                            —
                        @endif
                    </div>
                </div>

                <div class="info full">
                    <div class="label">Dentist Signature</div>
                    <div class="value">
                        @if($consent->dentist_signature_path)
                            <img
                                src="{{ asset('storage/'.$consent->dentist_signature_path) }}"
                                alt="Consent dentist signature"
                                style="max-width:520px;width:100%;border:1px solid rgba(15,23,42,.10);border-radius:12px;background:#fff;padding:8px;"
                            >
                        @else
                            <span class="muted">Not signed</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
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
