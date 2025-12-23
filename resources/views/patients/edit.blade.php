@extends('layouts.app')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);
    }

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
        font-weight: 700;
        letter-spacing: -0.3px;
        margin: 0;
        color: #0f172a;
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: rgba(15, 23, 42, .55);
    }

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

    .card-bodyx{
        padding: 18px;
    }

    .form-labelx{
        font-weight: 700;
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

    .btn-primaryx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        border: none;
        color: #fff;
        text-decoration: none;
        background: linear-gradient(135deg, #16a34a, #22c55e);
        box-shadow: 0 10px 18px rgba(34, 197, 94, .20);
        transition: .15s ease;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(34, 197, 94, .26);
        color:#fff;
    }

    .btn-ghostx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid rgba(15, 23, 42, .12);
        color: rgba(15, 23, 42, .75);
        background: rgba(255,255,255,.85);
        transition: .15s ease;
    }
    .btn-ghostx:hover{
        background: rgba(15, 23, 42, .04);
        color: rgba(15, 23, 42, .85);
    }

    .error-box{
        background: rgba(239, 68, 68, .10);
        border: 1px solid rgba(239, 68, 68, .22);
        color: #b91c1c;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    .error-box .title{
        font-weight: 800;
        margin-bottom: 6px;
    }
    .error-box ul{
        margin: 0;
        padding-left: 18px;
        font-size: 13px;
    }

    .form-max{
        max-width: 1100px;
    }
</style>

<div class="page-head">
    <div>
        <h2 class="page-title">Edit Patient</h2>
        <p class="subtitle">Update the patient’s information below.</p>
    </div>

    <a href="{{ route('patients.index') }}" class="btn-ghostx">
        <i class="fa fa-arrow-left"></i> Back to Patients
    </a>
</div>

@if ($errors->any())
    <div class="error-box">
        <div class="title"><i class="fa fa-triangle-exclamation"></i> Please fix the following:</div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card-shell form-max">
    <div class="card-head">
        <div class="hint">Editing: <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong></div>
        <div class="hint">Changes will be saved immediately</div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('patients.update', $patient->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label class="form-labelx">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="inputx"
                           value="{{ old('first_name', $patient->first_name) }}" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="inputx"
                           value="{{ old('last_name', $patient->last_name) }}" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Middle Name</label>
                    <input type="text" name="middle_name" class="inputx"
                           value="{{ old('middle_name', $patient->middle_name) }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Birthdate <span class="text-danger">*</span></label>
                    <input type="date" name="birthdate" class="inputx"
                           value="{{ old('birthdate', $patient->birthdate) }}" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="selectx" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male" {{ old('gender', $patient->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $patient->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $patient->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Contact Number</label>
                    <input type="text" name="contact_number" class="inputx"
                           value="{{ old('contact_number', $patient->contact_number) }}">
                    <div class="helper">Example: 09XXXXXXXXX</div>
                </div>

                <div class="col-12">
                    <label class="form-labelx">Address</label>
                    <textarea name="address" rows="2" class="textareax">{{ old('address', $patient->address) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-labelx">Notes</label>
                    <textarea name="notes" rows="3" class="textareax">{{ old('notes', $patient->notes) }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Update Patient
                    </button>

                    <a href="{{ route('patients.index') }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
