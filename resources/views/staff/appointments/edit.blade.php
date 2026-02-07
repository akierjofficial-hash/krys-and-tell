@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);
        --brand: #f59e0b;
        --brand2:#d97706;
        --ink:#0f172a;
        --muted: rgba(15, 23, 42, .55);
    }

    /* Header */
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -.04em;
        margin: 0;
        color: var(--ink);
        line-height: 1.05;
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
        max-width: 70ch;
        line-height: 1.6;
    }

    .btn-ghostx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 900;
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
        color: rgba(15, 23, 42, .90);
    }

    .btn-warnx{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap: 8px;
        padding: 12px 16px;
        border-radius: 14px;
        font-weight: 950;
        font-size: 14px;
        border: none;
        color: #fff;
        background: linear-gradient(135deg, var(--brand), var(--brand2));
        box-shadow: 0 12px 22px rgba(217,119,6,.20);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-warnx:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 30px rgba(217,119,6,.26);
        color:#fff;
    }

    /* Card */
    .card-shell{
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
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
    .card-bodyx{ padding: 18px; }

    .hint{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Badge */
    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(245,158,11,.22);
        background: rgba(245,158,11,.10);
        color: rgba(15,23,42,.88);
        white-space: nowrap;
    }

    /* Summary strip */
    .summary{
        display:flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 14px;
        border: 1px solid rgba(15,23,42,.08);
        background: rgba(15,23,42,.02);
        margin-bottom: 14px;
    }
    .summary .item{
        display:flex;
        align-items:center;
        gap: 10px;
        padding: 8px 10px;
        border-radius: 12px;
        background: rgba(255,255,255,.85);
        border: 1px solid rgba(15,23,42,.08);
    }
    .summary .ico{
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display:grid;
        place-items:center;
        background: rgba(245,158,11,.12);
        border: 1px solid rgba(245,158,11,.20);
        color: rgba(15,23,42,.90);
        flex: 0 0 auto;
    }
    .summary .label{
        font-size: 11px;
        font-weight: 900;
        color: rgba(15,23,42,.55);
        line-height: 1.1;
    }
    .summary .value{
        font-size: 13px;
        font-weight: 950;
        color: rgba(15,23,42,.90);
        line-height: 1.1;
        margin-top: 2px;
    }

    /* Inputs */
    .form-labelx{
        font-weight: 950;
        font-size: 13px;
        color: rgba(15, 23, 42, .78);
        margin-bottom: 6px;
    }
    .inputx, .selectx, .textareax{
        width: 100%;
        border: 1px solid rgba(15, 23, 42, .12);
        padding: 11px 12px;
        border-radius: 14px;
        font-size: 14px;
        color: #0f172a;
        background: rgba(255,255,255,.95);
        outline: none;
        transition: .15s ease;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }
    .textareax{ min-height: 90px; resize: vertical; }

    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: rgba(245,158,11,.60);
        box-shadow: 0 0 0 4px rgba(245,158,11,.14);
        background: #fff;
    }

    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
        line-height: 1.5;
    }

    .form-max{ max-width: 1100px; }

    /* Sticky action bar (mobile) */
    .actionbar{
        display:flex;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: 6px;
    }
    @media (max-width: 768px){
        .actionbar{
            position: sticky;
            bottom: 10px;
            background: linear-gradient(to top, rgba(255,255,255,.96), rgba(255,255,255,.80));
            padding: 12px;
            border-radius: 16px;
            border: 1px solid rgba(15,23,42,.08);
            box-shadow: 0 18px 40px rgba(15,23,42,.10);
            z-index: 20;
        }
        .actionbar .btn-warnx,
        .actionbar .btn-ghostx{
            flex: 1 1 auto;
            width: 100%;
        }
    }
</style>

@php
    // pick selected doctor id:
    // 1) old input doctor_id
    // 2) appointment doctor_id
    // 3) map dentist_name -> doctor list
    $selectedDoctorId = old('doctor_id');

    if (blank($selectedDoctorId)) {
        $selectedDoctorId = $appointment->doctor_id;
    }

    if (blank($selectedDoctorId) && !empty($appointment->dentist_name ?? null)) {
        $match = $doctors->firstWhere('name', $appointment->dentist_name);
        $selectedDoctorId = $match?->id;
    }

    // simple strings for summary
    $patientName = optional($appointment->patient)->first_name.' '.optional($appointment->patient)->last_name;
    $serviceName = optional($appointment->service)->name;
    $doctorLabel = optional($appointment->doctor)->name ?? ($appointment->dentist_name ?? '—');
@endphp

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Edit Appointment</h2>
        <p class="subtitle">
            Update the appointment details. Any changes you make here (doctor, date, time) will reflect on the patient’s public account record.
        </p>
    </div>

    <x-back-button
        fallback="{{ route('staff.appointments.index') }}"
        class="btn-ghostx"
        label="Back to Appointments"
    />
</div>

@if ($errors->any())
    <div class="form-max mb-3">
        <div class="alert alert-danger mb-0" style="border-radius: 14px;">
            <div class="fw-bold mb-1">Please fix the following:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="card-shell form-max">
    <div class="card-head">
        <div class="hint">
            <span class="badge-soft"><i class="fa fa-pen-to-square"></i> Editing Appointment #{{ $appointment->id }}</span>
            <span class="badge-soft" style="border-color: rgba(15,23,42,.12); background: rgba(15,23,42,.04);">
                <i class="fa fa-rotate"></i> Syncs to Patient View
            </span>
        </div>
        <div class="hint">Time uses 5-minute steps (step="300").</div>
    </div>

    <div class="card-bodyx">

        {{-- quick summary --}}
        <div class="summary">
            <div class="item">
                <div class="ico"><i class="fa-regular fa-user"></i></div>
                <div>
                    <div class="label">Patient</div>
                    <div class="value">{{ trim($patientName) ?: '—' }}</div>
                </div>
            </div>
            <div class="item">
                <div class="ico"><i class="fa-solid fa-tooth"></i></div>
                <div>
                    <div class="label">Service</div>
                    <div class="value">{{ $serviceName ?: '—' }}</div>
                </div>
            </div>
            <div class="item">
                <div class="ico"><i class="fa-solid fa-user-doctor"></i></div>
                <div>
                    <div class="label">Doctor</div>
                    <div class="value">{{ $doctorLabel }}</div>
                </div>
            </div>
            <div class="item">
                <div class="ico"><i class="fa-regular fa-calendar"></i></div>
                <div>
                    <div class="label">Date</div>
                    <div class="value">{{ $appointment->appointment_date ?: '—' }}</div>
                </div>
            </div>
            <div class="item">
                <div class="ico"><i class="fa-regular fa-clock"></i></div>
                <div>
                    <div class="label">Time</div>
                    <div class="value">{{ $appointment->appointment_time ?: '—' }}</div>
                </div>
            </div>
        </div>

        <form action="{{ route('staff.appointments.update', $appointment) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                <!-- Patient -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Select Patient <span class="text-danger">*</span></label>
                    <select name="patient_id" class="selectx" required>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" @selected((int)old('patient_id', $appointment->patient_id) === (int)$patient->id)>
                                {{ $patient->first_name }} {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="helper">Changing patient will also re-link this appointment to that patient’s public account (if email matches a user).</div>
                </div>

                <!-- Service -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Select Service <span class="text-danger">*</span></label>
                    <select name="service_id" class="selectx" required>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" @selected((int)old('service_id', $appointment->service_id) === (int)$service->id)>
                                {{ $service->name }} (₱{{ number_format($service->base_price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dentist / Doctor -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Assigned Dentist <span class="text-danger">*</span></label>

                    @if($doctors->count() > 0)
                        {{-- ✅ Save doctor_id (best for patient/public sync) --}}
                        <select name="doctor_id" class="selectx" required>
                            <option value="">-- Choose Dentist --</option>

                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}"
                                    @selected((int)old('doctor_id', $selectedDoctorId) === (int)$doc->id)
                                >
                                    {{ $doc->name }}{{ $doc->specialty ? ' — '.$doc->specialty : '' }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Keep dentist_name for backward compatibility (controller syncs it anyway) --}}
                        <input type="hidden" name="dentist_name" value="{{ old('dentist_name', $appointment->dentist_name) }}">

                        <div class="helper">
                            Managed from Admin → Doctors. Choosing here updates <strong>doctor_id</strong>, which ensures the patient sees the updated doctor correctly.
                        </div>
                    @else
                        {{-- fallback if no active doctors --}}
                        <input type="hidden" name="doctor_id" value="">
                        <input type="text" name="dentist_name" class="inputx"
                               value="{{ old('dentist_name', $appointment->dentist_name) }}"
                               placeholder="Enter dentist name (no active doctors available)" required>
                        <div class="helper">
                            No active doctors found. Add doctors in Admin → Doctors to enable the dropdown.
                        </div>
                    @endif
                </div>

                <!-- Date -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Appointment Date <span class="text-danger">*</span></label>
                    <input type="date" name="appointment_date"
                           value="{{ old('appointment_date', $appointment->appointment_date) }}"
                           class="inputx" required>
                </div>

                <!-- Time -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Appointment Time <span class="text-danger">*</span></label>
                    <input type="time" name="appointment_time"
                           value="{{ old('appointment_time', $appointment->appointment_time) }}"
                           class="inputx" required step="300">
                    <div class="helper">Uses 5-minute steps.</div>
                </div>

                <!-- Status -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Status <span class="text-danger">*</span></label>
                    @php $st = old('status', $appointment->status); @endphp
                    <select name="status" class="selectx" required>
                        <option value="pending"   @selected($st === 'pending')>Pending</option>
                        <option value="approved"  @selected($st === 'approved')>Approved</option>
                        <option value="confirmed" @selected($st === 'confirmed')>Confirmed</option>
                        <option value="scheduled" @selected($st === 'scheduled')>Scheduled</option>
                        <option value="completed" @selected($st === 'completed' || $st === 'done')>Completed</option>
                        <option value="canceled"  @selected($st === 'canceled' || $st === 'cancelled')>Canceled</option>
                        <option value="declined"  @selected($st === 'declined' || $st === 'rejected')>Declined</option>
                    </select>
                    <div class="helper">Public side typically shows the latest status after refresh.</div>
                </div>

                <!-- Notes -->
                <div class="col-12">
                    <label class="form-labelx">Notes</label>
                    <textarea name="notes" class="textareax" placeholder="Optional notes...">{{ old('notes', $appointment->notes) }}</textarea>
                </div>

                <div class="col-12 actionbar">
                    <button type="submit" class="btn-warnx">
                        <i class="fa fa-check"></i> Update Appointment
                    </button>
                    <a href="{{ route('staff.appointments.index') }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection
