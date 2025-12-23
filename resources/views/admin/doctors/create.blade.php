@extends('layouts.admin')

@push('styles')
<style>
    .head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 8px 0 14px;
    }
    .head h2{
        margin:0;
        font-weight: 950;
        letter-spacing: -.35px;
        font-size: 22px;
    }
    .sub{
        margin-top: 4px;
        color: var(--muted);
        font-weight: 800;
        font-size: 13px;
    }

    .back-btn{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(148,163,184,.28);
        background: rgba(148,163,184,.06);
        color: var(--text);
        font-weight: 950;
        text-decoration:none;
        transition: .15s ease;
        white-space: nowrap;
    }
    .back-btn:hover{
        transform: translateY(-1px);
        background: rgba(21,90,193,.08);
        border-color: rgba(21,90,193,.25);
    }

    .form-card{ padding: 16px; }
    .section{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .section .title{
        font-weight: 950;
        letter-spacing: -.2px;
        margin:0;
        font-size: 14px;
    }
    .section .hint{
        color: var(--muted);
        font-weight: 800;
        font-size: 12px;
    }

    .form-label{
        font-weight: 950;
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .kt-control{
        border-radius: 12px !important;
        border: 1px solid rgba(148,163,184,.28) !important;
        background: transparent !important;
        color: var(--text) !important;
        font-weight: 850 !important;
        padding: 11px 12px !important;
        box-shadow: none !important;
        transition: .15s ease;
    }
    .kt-control:focus{
        border-color: rgba(21,90,193,.40) !important;
        box-shadow: 0 0 0 4px rgba(21,90,193,.10) !important;
    }

    .toggle-wrap{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid rgba(148,163,184,.20);
        background: rgba(148,163,184,.06);
    }
    html[data-theme="dark"] .toggle-wrap{ background: rgba(2,6,23,.25); }

    .toggle-left .t{
        font-weight: 950;
        letter-spacing: -.2px;
    }
    .toggle-left .s{
        margin-top: 2px;
        color: var(--muted);
        font-weight: 800;
        font-size: 12px;
    }

    .actions{
        display:flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
        justify-content:flex-end;
    }
    .btnx{
        border-radius: 12px;
        font-weight: 950;
        padding: 10px 14px;
        display:inline-flex;
        align-items:center;
        gap:8px;
        transition: .15s ease;
        text-decoration:none;
        white-space: nowrap;
    }
    .btnx:hover{ transform: translateY(-1px); }

    .btn-primaryx{
        background: var(--primary);
        color: #fff;
        border: 1px solid rgba(21,90,193,.35);
        box-shadow: 0 10px 22px rgba(21,90,193,.20);
    }
    .btn-primaryx:hover{ background: #0f4faa; }

    .btn-ghost{
        background: transparent;
        color: var(--text);
        border: 1px solid rgba(148,163,184,.28);
    }
    .btn-ghost:hover{
        background: rgba(21,90,193,.08);
        border-color: rgba(21,90,193,.25);
    }

    .alert{ border-radius: 12px; }
</style>
@endpush

@section('content')

<div class="head">
    <div>
        <h2>Create Doctor</h2>
        <div class="sub">Add associate doctors that will appear in Staff scheduling.</div>
    </div>

    <a href="{{ route('admin.doctors.index') }}" class="back-btn">
        <i class="fa fa-arrow-left"></i> Back
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <div style="font-weight:950;">Please fix the following:</div>
        <ul class="mb-0" style="font-weight:800;">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="cardx form-card">
    <div class="section">
        <div>
            <p class="title">Doctor Information</p>
            <div class="hint">Tip: Only Active doctors will show in Staff side dropdowns.</div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.doctors.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input class="form-control kt-control" name="name" value="{{ old('name') }}" placeholder="e.g. Dr. Juan Dela Cruz" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Specialty</label>
                <input class="form-control kt-control" name="specialty" value="{{ old('specialty') }}" placeholder="e.g. Dentist / Orthodontist">
            </div>

            <div class="col-md-6">
                <label class="form-label">Email (optional)</label>
                <input class="form-control kt-control" type="email" name="email" value="{{ old('email') }}" placeholder="e.g. doctor@email.com">
            </div>

            <div class="col-md-6">
                <label class="form-label">Phone (optional)</label>
                <input class="form-control kt-control" name="phone" value="{{ old('phone') }}" placeholder="e.g. 09xxxxxxxxx">
            </div>

            <div class="col-12">
                <div class="toggle-wrap">
                    <div class="toggle-left">
                        <div class="t">Active Status</div>
                        <div class="s">Inactive doctors won’t show up in booking/scheduling lists.</div>
                    </div>

                    <div class="form-check form-switch" style="margin:0;">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                               @checked(old('is_active', 1))>
                        <label class="form-check-label" for="is_active" style="font-weight:900;">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btnx btn-primaryx" type="submit">
                <i class="fa fa-save"></i> Create Doctor
            </button>
            <a href="{{ route('admin.doctors.index') }}" class="btnx btn-ghost">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection
