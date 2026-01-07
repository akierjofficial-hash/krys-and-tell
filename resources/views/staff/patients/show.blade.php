@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);
        --muted: rgba(15, 23, 42, .55);
    }

    .wrap{
        max-width: 1220px;
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
        font-weight: 900;
        letter-spacing: -0.3px;
        margin: 0;
        color: #0f172a;
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--muted);
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
        color: rgba(15, 23, 42, .80);
        background: rgba(255,255,255,.88);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-ghostx:hover{
        background: rgba(15, 23, 42, .04);
        color: rgba(15, 23, 42, .92);
    }
    .btn-primaryx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 900;
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
    .cardx{
        background: rgba(255,255,255,.94);
        border: var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .cardx + .cardx{ margin-top: 14px; }

    .cardx-head{
        padding: 14px 16px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .cardx-title{
        margin: 0;
        font-weight: 1000;
        font-size: 14px;
        color: #0f172a;
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .cardx-hint{
        font-size: 12px;
        color: var(--muted);
    }
    .cardx-body{
        padding: 16px;
    }

    /* Left profile */
    .avatar{
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display:grid;
        place-items:center;
        font-weight: 1000;
        color: #0d6efd;
        background: rgba(13,110,253,.10);
        border: 1px solid rgba(13,110,253,.18);
    }
    .pname{
        margin: 0;
        font-weight: 1000;
        letter-spacing: -0.2px;
        color:#0f172a;
        font-size: 18px;
        line-height: 1.1;
    }
    .pmeta{
        margin-top: 6px;
        font-size: 12px;
        color: var(--muted);
        display:flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items:center;
    }

    .info-grid{
        display:grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .info{
        padding: 12px;
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248,250,252,.86);
    }
    .info .label{
        font-size: 11px;
        font-weight: 900;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .35px;
        margin-bottom: 6px;
    }
    .info .value{
        font-size: 14px;
        font-weight: 800;
        color:#0f172a;
        word-break: break-word;
    }

    /* Chips */
    .chip{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 1000;
        border: 1px solid transparent;
        white-space: nowrap;
        line-height: 1;
    }
    .chip-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .chip-male{ background: rgba(59,130,246,.12); color:#1d4ed8; border-color: rgba(59,130,246,.25); }
    .chip-female{ background: rgba(236,72,153,.12); color:#be185d; border-color: rgba(236,72,153,.25); }
    .chip-other{ background: rgba(107,114,128,.12); color: rgba(15,23,42,.78); border-color: rgba(107,114,128,.25); }

    .chip-yes{ background: rgba(16,185,129,.14); color:#047857; border-color: rgba(16,185,129,.25); }
    .chip-no{ background: rgba(107,114,128,.12); color: rgba(15,23,42,.70); border-color: rgba(107,114,128,.25); }

    /* Visit services pills */
    .svc-pill{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 1000;
        background: rgba(13,110,253,.10);
        color: #0d6efd;
        border: 1px solid rgba(13,110,253,.25);
        white-space: nowrap;
        line-height: 1;
    }
    .svc-wrap{ display:flex; gap: 6px; flex-wrap: wrap; }

    /* Tabs */
    .tabsx{
        border-bottom: 1px solid rgba(15,23,42,.10);
        margin: -6px -6px 12px -6px;
        padding: 6px 6px 0 6px;
        display:flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .tabx{
        border: 1px solid rgba(15,23,42,.12);
        background: rgba(255,255,255,.92);
        padding: 8px 12px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 13px;
        color: rgba(15,23,42,.80);
        cursor:pointer;
        user-select:none;
    }
    .tabx.active{
        background: rgba(13,110,253,.10);
        border-color: rgba(13,110,253,.25);
        color:#0d6efd;
    }

    .tabpane{ display:none; }
    .tabpane.active{ display:block; }

    /* Tables */
    .table-wrap{ border-radius: 14px; overflow:hidden; border: 1px solid rgba(15,23,42,.08); }
    table{ width:100%; border-collapse: separate; border-spacing:0; }
    thead th{
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: var(--muted);
        padding: 12px 12px;
        background: rgba(248,250,252,.95);
        border-bottom: 1px solid rgba(15,23,42,.08);
        white-space: nowrap;
    }
    tbody td{
        padding: 12px 12px;
        font-size: 14px;
        color:#0f172a;
        border-bottom: 1px solid rgba(15,23,42,.06);
        vertical-align: middle;
    }
    tbody tr:hover{ background: rgba(13,110,253,.06); }

    .sig-img{
        max-width: 520px;
        width: 100%;
        border: 1px solid rgba(15,23,42,.10);
        border-radius: 12px;
        background: #fff;
        padding: 8px;
    }
    .muted{ color: var(--muted); }
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

    // Consent YES/NO resolver:
    // - New system saves "Yes"/"No"
    // - Old system may have initials like "AK" -> treat as YES
    $consentYes = function($val){
        if ($val === null) return false;
        $v = strtolower(trim((string)$val));
        if ($v === '') return false;
        if (in_array($v, ['no','0','false'], true)) return false;
        if (in_array($v, ['yes','1','true'], true)) return true;
        // any initials/text = YES
        return true;
    };
@endphp

<div class="page-head wrap">
    <div>
        <h2 class="page-title">Patient Details</h2>
        <p class="subtitle">Organized view of profile, information record, consent, visits, appointments, and payments</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <x-back-button
            fallback="{{ route('staff.patients.index') }}"
            class="btn-ghostx"
            label="Back"
        />

        <a href="{{ route('staff.patients.printInfo', $patient->id) }}" target="_blank" class="btn-ghostx">
            <i class="fa fa-print"></i> Print Patient Info (PDF)
        </a>

        <a href="{{ route('staff.patients.edit', [$patient->id, 'return' => url()->full()]) }}" class="btn-primaryx">
            <i class="fa fa-pen"></i> Edit Patient
        </a>
    </div>
</div>

<div class="wrap">
    <div class="row g-3">

        {{-- LEFT: PROFILE SUMMARY --}}
        <div class="col-12 col-lg-4">
            <div class="cardx">
                <div class="cardx-head">
                    <div class="cardx-title"><i class="fa fa-user"></i> Profile</div>
                    <div class="cardx-hint">ID: <strong>#{{ $patient->id }}</strong></div>
                </div>

                <div class="cardx-body">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="avatar">{{ $initials }}</div>
                        <div>
                            <h3 class="pname">{{ $fullName ?: '—' }}</h3>
                            <div class="pmeta">
                                @if($patient->gender)
                                    <span class="chip {{ $genderClass }}">
                                        <span class="chip-dot"></span> {{ ucfirst($patient->gender) }}
                                    </span>
                                @else
                                    <span class="muted">Gender: —</span>
                                @endif

                                <span class="muted">•</span>

                                <span class="muted">
                                    Birthdate:
                                    {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('M d, Y') : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="info-grid mt-3">
                        <div class="info">
                            <div class="label">Contact Number</div>
                            <div class="value">{{ $patient->contact_number ?? '—' }}</div>
                        </div>

                        <div class="info">
                            <div class="label">Email</div>
                            <div class="value">{{ $patient->email ?? '—' }}</div>
                        </div>

                        <div class="info">
                            <div class="label">Address</div>
                            <div class="value">{{ $patient->address ?? '—' }}</div>
                        </div>

                        <div class="info">
                            <div class="label">Notes</div>
                            <div class="value">{{ $patient->notes ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- QUICK STATUS --}}
            <div class="cardx mt-3">
                <div class="cardx-head">
                    <div class="cardx-title"><i class="fa fa-circle-info"></i> Record Status</div>
                </div>
                <div class="cardx-body">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between">
                            <span class="muted">Info Record</span>
                            @if(optional($info)->signed_at)
                                <span class="chip chip-yes"><span class="chip-dot"></span> Signed</span>
                            @else
                                <span class="chip chip-no"><span class="chip-dot"></span> Not signed</span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="muted">Consent</span>
                            @if(optional($consent)->patient_signed_at)
                                <span class="chip chip-yes"><span class="chip-dot"></span> Patient signed</span>
                            @else
                                <span class="chip chip-no"><span class="chip-dot"></span> Not signed</span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="muted">Total Paid</span>
                            <span style="font-weight:1000;">₱{{ number_format((float) $totalPaid, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: TABS CONTENT --}}
        <div class="col-12 col-lg-8">
            <div class="cardx">
                <div class="cardx-head">
                    <div class="cardx-title"><i class="fa fa-layer-group"></i> Patient Records</div>
                    <div class="cardx-hint">Everything in one place (clean)</div>
                </div>

                <div class="cardx-body">
                    <div class="tabsx" id="tabsx">
                        <div class="tabx active" data-tab="tab-info">Info Record</div>
                        <div class="tabx" data-tab="tab-consent">Consent</div>
                        <div class="tabx" data-tab="tab-visits">Visits ({{ $visits->total() }})</div>
                        <div class="tabx" data-tab="tab-appts">Appointments ({{ $appointments->total() }})</div>
                        <div class="tabx" data-tab="tab-payments">Payments ({{ $payments->total() }})</div>
                    </div>

                    {{-- INFO RECORD --}}
                    <div class="tabpane active" id="tab-info">
                        @if(!$info)
                            <div class="muted">No Patient Information Record saved yet.</div>
                        @else
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <div class="info">
                                        <div class="label">Nickname</div>
                                        <div class="value">{{ $info->nickname ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="info">
                                        <div class="label">Occupation</div>
                                        <div class="value">{{ $info->occupation ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="info">
                                        <div class="label">Dental Insurance</div>
                                        <div class="value">{{ $info->dental_insurance ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="info">
                                        <div class="label">Effective Date</div>
                                        <div class="value">{{ $info->effective_date ? \Carbon\Carbon::parse($info->effective_date)->format('M d, Y') : '—' }}</div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="info">
                                        <div class="label">Home No.</div>
                                        <div class="value">{{ $info->home_no ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="info">
                                        <div class="label">Office No.</div>
                                        <div class="value">{{ $info->office_no ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="info">
                                        <div class="label">Fax No.</div>
                                        <div class="value">{{ $info->fax_no ?? '—' }}</div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info">
                                        <div class="label">Minor?</div>
                                        <div class="value">{{ $info->is_minor ? 'Yes' : 'No' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="info">
                                        <div class="label">Guardian</div>
                                        <div class="value">
                                            {{ $info->guardian_name ?? '—' }}
                                            @if($info->guardian_occupation)
                                                <div class="muted" style="margin-top:4px;">Occupation: {{ $info->guardian_occupation }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="info">
                                        <div class="label">Allergies</div>
                                        <div class="value">
                                            @php $all = $info->allergies ?? []; @endphp
                                            @if(!empty($all))
                                                {{ implode(', ', $all) }}
                                            @else
                                                —
                                            @endif

                                            @if($info->allergies_other)
                                                <div class="muted" style="margin-top:6px;">Other: {{ $info->allergies_other }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="info">
                                        <div class="label">Medical Conditions</div>
                                        <div class="value">
                                            @php $conds = $info->medical_conditions ?? []; @endphp
                                            @if(!empty($conds))
                                                {{ implode(', ', $conds) }}
                                            @else
                                                —
                                            @endif

                                            @if($info->medical_conditions_other)
                                                <div class="muted" style="margin-top:6px;">Other: {{ $info->medical_conditions_other }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="info">
                                        <div class="label">Signature</div>
                                        <div class="value">
                                            @if($info->signature_path)
                                                <img src="{{ asset('storage/'.$info->signature_path) }}" class="sig-img" alt="Patient signature">
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- CONSENT --}}
                    <div class="tabpane" id="tab-consent">
                        @if(!$consent)
                            <div class="muted">No Informed Consent saved yet.</div>
                        @else
                            <div class="muted mb-2">
                                @if($consent->patient_signed_at)
                                    Patient signed: <strong>{{ \Carbon\Carbon::parse($consent->patient_signed_at)->format('M d, Y h:i A') }}</strong>
                                @else
                                    Patient not signed yet.
                                @endif
                            </div>

                            <div class="table-wrap table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Section</th>
                                            <th style="width:160px;">Consent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($consentLabels as $key => $label)
                                            @php
                                                $val = $consent->initials[$key] ?? null;
                                                $yes = $consentYes($val);
                                            @endphp
                                            <tr>
                                                <td style="font-weight:900;">{{ $label }}</td>
                                                <td>
                                                    @if($yes)
                                                        <span class="chip chip-yes"><span class="chip-dot"></span> YES</span>
                                                    @else
                                                        <span class="chip chip-no"><span class="chip-dot"></span> NO</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row g-2 mt-3">
                                <div class="col-12 col-md-6">
                                    <div class="info">
                                        <div class="label">Patient/Guardian Signature</div>
                                        <div class="value">
                                            @if($consent->patient_signature_path)
                                                <img src="{{ asset('storage/'.$consent->patient_signature_path) }}" class="sig-img" alt="Consent patient signature">
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="info">
                                        <div class="label">Dentist Signature</div>
                                        <div class="value">
                                            @if($consent->dentist_signature_path)
                                                <img src="{{ asset('storage/'.$consent->dentist_signature_path) }}" class="sig-img" alt="Consent dentist signature">
                                            @else
                                                <span class="muted">Not signed</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- VISITS --}}
                    <div class="tabpane" id="tab-visits">
                        @if($visits->isEmpty())
                            <div class="muted">No visits found.</div>
                        @else
                            <div class="table-wrap table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Visit Date</th>
                                            <th>Services</th>
                                            <th>Notes / Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($visits as $visit)
                                            @php
                                                $serviceNames = $visit->procedures
                                                    ->map(fn($p) => optional($p->service)->name)
                                                    ->filter()
                                                    ->unique()
                                                    ->values();
                                            @endphp

                                            <tr>
                                                <td style="font-weight:900;">
                                                    {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') : '—' }}
                                                </td>

                                                <td>
                                                    @if($serviceNames->count())
                                                        <div class="svc-wrap">
                                                            @foreach($serviceNames as $name)
                                                                <span class="svc-pill">{{ $name }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="muted">—</span>
                                                    @endif
                                                </td>

                                                <td class="muted">
                                                    {{ $visit->notes ?? $visit->description ?? '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($visits->hasPages())
                            <div class="mt-3">
                                {{ $visits->appends(request()->except('visits_page'))->links() }}
                            </div>
                        @endif
                    </div>

                    {{-- APPOINTMENTS --}}
                    <div class="tabpane" id="tab-appts">
                        @if($appointments->isEmpty())
                            <div class="muted">No appointments found.</div>
                        @else
                            <div class="table-wrap table-responsive">
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
                                            <tr>
                                                <td style="font-weight:900;">
                                                    {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') : '—' }}
                                                </td>
                                                <td class="muted">
                                                    {{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : '—' }}
                                                </td>
                                                <td class="muted">{{ optional($appointment->service)->name ?? '—' }}</td>
                                                <td class="muted">{{ $appointment->dentist_name ?? '—' }}</td>
                                                <td style="font-weight:900;">{{ $appointment->status ?? '—' }}</td>
                                                <td class="muted">{{ $appointment->notes ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($appointments->hasPages())
                            <div class="mt-3">
                                {{ $appointments->appends(request()->except('appointments_page'))->links() }}
                            </div>
                        @endif
                    </div>

                    {{-- PAYMENTS --}}
                    <div class="tabpane" id="tab-payments">
                        @if($payments->isEmpty())
                            <div class="muted">No payments found.</div>
                        @else
                            <div class="table-wrap table-responsive">
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
                                                <td style="font-weight:900;">
                                                    {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : '—' }}
                                                </td>
                                                <td class="muted">
                                                    {{ optional($payment->visit)->visit_date ? \Carbon\Carbon::parse($payment->visit->visit_date)->format('M d, Y') : '—' }}
                                                </td>
                                                <td style="font-weight:1000;">₱{{ number_format((float) $payment->amount, 2) }}</td>
                                                <td class="muted">{{ ucfirst($payment->method ?? '—') }}</td>
                                                <td class="muted">{{ $payment->notes ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($payments->hasPages())
                            <div class="mt-3">
                                {{ $payments->appends(request()->except('payments_page'))->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // simple tabs
    (function(){
        const tabs = document.querySelectorAll('.tabx');
        const panes = document.querySelectorAll('.tabpane');

        function activate(id){
            tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === id));
            panes.forEach(p => p.classList.toggle('active', p.id === id));
        }

        tabs.forEach(t => {
            t.addEventListener('click', () => activate(t.dataset.tab));
        });
    })();
</script>

@endsection
