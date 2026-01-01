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
        font-weight: 900;
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
        color: rgba(15, 23, 42, .85);
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
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13,110,253,.18);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13,110,253,.24);
        color:#fff;
    }

    /* Card */
    .card-shell{
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        border-radius: 16px;
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
    .card-head .hint{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }
    .card-bodyx{ padding: 18px; }

    /* Inputs */
    .form-labelx{
        font-weight: 900;
        font-size: 13px;
        color: rgba(15, 23, 42, .75);
        margin-bottom: 6px;
    }
    .inputx, .selectx, .textareax{
        width: 100%;
        border: 1px solid rgba(15, 23, 42, .12);
        padding: 11px 12px;
        border-radius: 12px;
        font-size: 14px;
        color: #0f172a;
        background: rgba(255,255,255,.95);
        outline: none;
        transition: .15s ease;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }
    .textareax{ min-height: 90px; resize: vertical; }

    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: #fff;
    }

    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }

    /* Small badge */
    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid rgba(59,130,246,.22);
        background: rgba(59,130,246,.10);
        color: rgba(15,23,42,.85);
        white-space: nowrap;
    }

    .form-max{ max-width: 1100px; }
</style>

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Add Appointment</h2>
        <p class="subtitle">Fill out the form below to schedule a new appointment.</p>
    </div>

    <a href="{{ route('staff.appointments.index') }}" class="btn-ghostx">
        <i class="fa fa-arrow-left"></i> Back to Appointments
    </a>
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
            <span class="badge-soft"><i class="fa fa-calendar-plus"></i> New Appointment</span>
        </div>
        <div class="hint">Tip: use a 5-minute step time slot.</div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('staff.appointments.store') }}" method="POST">
            @csrf

            <div class="row g-3">

                <!-- Patient -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Select Patient <span class="text-danger">*</span></label>
                    <select name="patient_id" class="selectx" required>
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->first_name }} {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Service -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Select Service <span class="text-danger">*</span></label>
                    <select name="service_id" class="selectx" required>
                        <option value="">-- Select Service --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }} (₱{{ number_format($service->base_price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dentist -->
<div class="col-12 col-md-6">
    <label class="form-labelx">Assigned Dentist <span class="text-danger">*</span></label>

    <select name="dentist_name" class="selectx" required>
        <option value="">-- Choose Dentist --</option>

        @forelse($doctors as $doc)
            <option
                value="{{ $doc->name }}"
                @selected(old('dentist_name') == $doc->name)
            >
                {{ $doc->name }}{{ $doc->specialty ? ' — '.$doc->specialty : '' }}
            </option>
        @empty
            <option value="" disabled>No active doctors yet (add from Admin → Doctors)</option>
        @endforelse
    </select>

    <div class="helper">
        This list is managed from Admin → Doctors. Only Active doctors appear here.
    </div>
</div>


                <!-- Appointment Date -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Appointment Date <span class="text-danger">*</span></label>
                    <input type="date" name="appointment_date" class="inputx" value="{{ old('appointment_date') }}" required>
                </div>

                <!-- Appointment Time -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Appointment Time <span class="text-danger">*</span></label>
                    <input type="time" name="appointment_time" class="inputx" value="{{ old('appointment_time') }}" required step="300">
                    <div class="helper">Time will be displayed in AM/PM format.</div>
                </div>

                <!-- Status -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Status <span class="text-danger">*</span></label>
                    <select name="status" class="selectx" required>
                        <option value="scheduled" {{ old('status','scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="canceled" {{ old('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="col-12">
                    <label class="form-labelx">Notes</label>
                    <textarea name="notes" class="textareax">{{ old('notes') }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Save Appointment
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
