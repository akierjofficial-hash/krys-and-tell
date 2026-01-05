@extends('layouts.staff')

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
    .card-head .hint{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }

    .card-bodyx{ padding: 18px; }

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
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13, 110, 253, .20);
        transition: .15s ease;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .26);
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
    .error-box .title{ font-weight: 800; margin-bottom: 6px; }
    .error-box ul{ margin: 0; padding-left: 18px; font-size: 13px; }

    .warn-box{
        background: rgba(245, 158, 11, .12);
        border: 1px solid rgba(245, 158, 11, .28);
        color: rgba(120, 53, 15, 1);
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    .warn-title{ font-weight: 900; margin-bottom: 6px; }
    .warn-sub{ font-size: 13px; opacity: .9; }

    .form-max{ max-width: 1100px; }

    .section-title{
        font-size: 14px;
        font-weight: 900;
        color: #0f172a;
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .badge-mini{
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 999px;
        background: rgba(15,23,42,.06);
        color: rgba(15,23,42,.75);
        border: 1px solid rgba(15,23,42,.08);
    }

    .radio-row{
        display:flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 6px;
    }
    .radio-pill{
        border: 1px solid rgba(15,23,42,.12);
        border-radius: 999px;
        padding: 8px 10px;
        display:inline-flex;
        align-items:center;
        gap: 8px;
        background: rgba(255,255,255,.95);
        cursor:pointer;
        user-select:none;
    }

    .checks-grid{
        display:grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    @media (max-width: 992px){
        .checks-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 576px){
        .checks-grid{ grid-template-columns: 1fr; }
    }

    .check-item{
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.92);
        border-radius: 12px;
        padding: 10px 12px;
        display:flex;
        align-items:flex-start;
        gap: 10px;
    }
    .check-item input{
        margin-top: 3px;
        transform: scale(1.15);
    }
    .check-item .txt{
        font-size: 13px;
        color: rgba(15,23,42,.86);
        font-weight: 700;
        line-height: 1.25;
    }

    .sig-wrap{
        border:1px solid rgba(15,23,42,.12);
        border-radius:12px;
        overflow:hidden;
        background:#fff;
    }
    canvas.sig{
        width:100%;
        height:180px;
        display:block;
        touch-action:none;
    }
</style>

<div class="page-head">
    <div>
        <h2 class="page-title">Add New Patient</h2>
        <p class="subtitle">Patient fills this on iPad. Required fields are marked with <span class="text-danger">*</span>.</p>
    </div>

    <a href="{{ route('staff.patients.index') }}" class="btn-ghostx">
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

{{-- ✅ Duplicate warning --}}
@if (session('duplicate_candidates'))
    <div class="warn-box form-max">
        <div class="warn-title">
            <i class="fa fa-triangle-exclamation"></i>
            Possible duplicate patient found
        </div>
        <div class="warn-sub mb-2">
            A patient with the same name/birthdate or contact number already exists. Please review before creating.
        </div>

        <div class="list-group mb-3" style="border-radius:14px; overflow:hidden;">
            @foreach(session('duplicate_candidates') as $p)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">
                            {{ $p->first_name }} {{ $p->middle_name }} {{ $p->last_name }}
                        </div>
                        <div class="small text-muted">
                            Birthdate:
                            {{ $p->birthdate ? \Carbon\Carbon::parse($p->birthdate)->format('M d, Y') : '—' }}
                            • Contact: {{ $p->contact_number ?? '—' }}
                            • ID: {{ $p->id }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('staff.patients.show', $p->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:10px;font-weight:800;">
                            View
                        </a>
                        <a href="{{ route('staff.patients.edit', $p->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:10px;font-weight:800;">
                            Edit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-danger" id="forceCreateBtn" style="border-radius:12px;font-weight:900;">
                Create Anyway
            </button>
            <a href="{{ route('staff.patients.index') }}" class="btn btn-outline-secondary" style="border-radius:12px;font-weight:900;">
                Cancel
            </a>
        </div>
    </div>
@endif

@php
    $oldAllergies = old('allergies', []);
    $oldConditions = old('medical_conditions', []);
    $oldInitials = old('consent_initials', []);

    $allergyOptions = [
        'Local Anesthetic',
        'Aspirin',
        'Penicillin',
        'Sulfa Drugs',
        'Latex',
    ];

    $medicalConditionOptions = [
        'High Blood Pressure',
        'Low Blood Pressure',
        'Epilepsy / Convulsions',
        'AIDS or HIV Infection',
        'Sexually Transmitted Disease',
        'Stomach Troubles / Ulcers',
        'Fainting Seizure',
        'Radiation Therapy',
        'Rapid Weight Loss',
        'Joint Replacement / Implant',
        'Heart Surgery',
        'Heart Attack',
        'Thyroid Problem',

        'Heart Murmur',
        'Hepatitis / Liver Disease',
        'Rheumatic Fever',
        'Hay Fever / Allergies',
        'Respiratory Problems',
        'Hepatitis / Jaundice',
        'Tuberculosis',
        'Swollen Ankles',
        'Kidney Disease',
        'Diabetes',
        'Chest Pain',
        'Stroke',

        'Cancer',
        'Anemia',
        'Angina',
        'Asthma',
        'Emphysema',
        'Bleeding Problems',
        'Blood Diseases',
        'Arthritis / Rheumatism',
    ];

    // You can rename/adjust these keys anytime; they are saved as JSON in consent_initials[]
    $consentSections = [
        'treatment'   => 'Treatment to be done (understand the plan + possible risks)',
        'meds'        => 'Drugs & medications (possible reactions / side effects)',
        'changes'     => 'Changes in treatment plan (may be required during procedure)',
        'radiograph'  => 'Radiographs / X-rays (limits + diagnostic value)',
        'removal'     => 'Removal of teeth (possible complications)',
        'crowns'      => 'Crowns / caps / bridges (risks and follow-ups)',
        'endo'        => 'Endodontics / root canal (risks and outcomes)',
        'perio'       => 'Periodontal disease (gum treatment options)',
        'fillings'    => 'Fillings (sensitivity, adjustments, future needs)',
        'dentures'    => 'Dentures (fit, soreness, adjustments)',
        'disclaimer'  => 'Acknowledgement (dentistry not exact science, consent granted)',
    ];
@endphp

<form action="{{ route('staff.patients.store') }}" method="POST" id="patientForm" class="form-max">
    @csrf

    {{-- ✅ This controls the “Create Anyway” --}}
    <input type="hidden" name="force_create" id="force_create" value="0">

    {{-- hidden fields where canvas signatures get saved --}}
    <input type="hidden" name="patient_info_signature" id="patient_info_signature">
    <input type="hidden" name="consent_patient_signature" id="consent_patient_signature">
    <input type="hidden" name="consent_dentist_signature" id="consent_dentist_signature">

    {{-- =======================
        CARD 1: BASIC PATIENT INFO
    ======================= --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title">
                <i class="fa fa-user"></i> Patient Information Record
                <span class="badge-mini">Required: name, birthdate, sex, address, mobile, email, nickname, occupation</span>
            </div>
            <div class="hint">Fields marked <span class="text-danger">*</span> are required.</div>
        </div>

        <div class="card-bodyx">
            <div class="row g-3">

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="inputx" value="{{ old('last_name') }}" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-labelx">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="inputx" value="{{ old('first_name') }}" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-labelx">Middle Name</label>
                    <input type="text" name="middle_name" class="inputx" value="{{ old('middle_name') }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Birthdate <span class="text-danger">*</span></label>
                    <input type="date" name="birthdate" class="inputx" value="{{ old('birthdate') }}" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Sex <span class="text-danger">*</span></label>
                    <select name="gender" class="selectx" required>
                        <option value="">-- Select --</option>
                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Nickname <span class="text-danger">*</span></label>
                    <input type="text" name="nickname" class="inputx" value="{{ old('nickname') }}" required>
                </div>

                <div class="col-12">
                    <label class="form-labelx">Home Address <span class="text-danger">*</span></label>
                    <textarea name="address" rows="2" class="textareax" required>{{ old('address') }}</textarea>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Occupation <span class="text-danger">*</span></label>
                    <input type="text" name="occupation" class="inputx" value="{{ old('occupation') }}" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Cell/Mobile No. <span class="text-danger">*</span></label>
                    <input type="text" name="contact_number" class="inputx" value="{{ old('contact_number') }}" required>
                    <div class="helper">Example: 09XXXXXXXXX</div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Email Add <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="inputx" value="{{ old('email') }}" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Home No.</label>
                    <input type="text" name="home_no" class="inputx" value="{{ old('home_no') }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Office No.</label>
                    <input type="text" name="office_no" class="inputx" value="{{ old('office_no') }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Fax No.</label>
                    <input type="text" name="fax_no" class="inputx" value="{{ old('fax_no') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Dental Insurance</label>
                    <input type="text" name="dental_insurance" class="inputx" value="{{ old('dental_insurance') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Effective Date</label>
                    <input type="date" name="effective_date" class="inputx" value="{{ old('effective_date') }}">
                </div>

                <div class="col-12">
                    <label class="form-labelx">Notes (Internal)</label>
                    <textarea name="notes" rows="3" class="textareax">{{ old('notes') }}</textarea>
                </div>

            </div>
        </div>
    </div>

    {{-- =======================
        CARD 2: MINOR + REFERRAL + REASON
    ======================= --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title">
                <i class="fa fa-id-card"></i> For Minors / Referral / Reason
            </div>
            <div class="hint">If minor is checked, guardian name + occupation become required.</div>
        </div>

        <div class="card-bodyx">
            <div class="row g-3">

                <div class="col-12">
                    <label class="radio-pill" style="width:100%; justify-content:space-between;">
                        <span style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="is_minor" id="is_minor" value="1" {{ old('is_minor') ? 'checked' : '' }}>
                            <span style="font-weight:900;">Patient is a Minor</span>
                        </span>
                        <span class="badge-mini">Guardian required</span>
                    </label>
                </div>

                <div class="col-12 col-md-6" id="guardian_name_wrap">
                    <label class="form-labelx">Parent/Guardian’s Name <span class="text-danger" id="guardian_name_star">*</span></label>
                    <input type="text" name="guardian_name" id="guardian_name" class="inputx" value="{{ old('guardian_name') }}">
                </div>

                <div class="col-12 col-md-6" id="guardian_occ_wrap">
                    <label class="form-labelx">Occupation <span class="text-danger" id="guardian_occ_star">*</span></label>
                    <input type="text" name="guardian_occupation" id="guardian_occupation" class="inputx" value="{{ old('guardian_occupation') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Whom may we thank for referring you?</label>
                    <input type="text" name="referral_source" class="inputx" value="{{ old('referral_source') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Reason for dental consultation?</label>
                    <input type="text" name="consultation_reason" class="inputx" value="{{ old('consultation_reason') }}">
                </div>

            </div>
        </div>
    </div>

    {{-- =======================
        CARD 3: DENTAL HISTORY
    ======================= --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title">
                <i class="fa fa-tooth"></i> Dental History
            </div>
            <div class="hint">Optional</div>
        </div>

        <div class="card-bodyx">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Previous Dentist / Dr.</label>
                    <input type="text" name="previous_dentist" class="inputx" value="{{ old('previous_dentist') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Last Dental Visit</label>
                    <input type="date" name="last_dental_visit" class="inputx" value="{{ old('last_dental_visit') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- =======================
        CARD 4: MEDICAL HISTORY
    ======================= --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title">
                <i class="fa fa-notes-medical"></i> Medical History
            </div>
            <div class="hint">Answer Yes/No where applicable.</div>
        </div>

        <div class="card-bodyx">
            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Name of Physician</label>
                    <input type="text" name="physician_name" class="inputx" value="{{ old('physician_name') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Specialty (if applicable)</label>
                    <input type="text" name="physician_specialty" class="inputx" value="{{ old('physician_specialty') }}">
                </div>

                {{-- Q1 --}}
                <div class="col-12">
                    <label class="form-labelx">1. Are you in good health?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="good_health" value="1" {{ old('good_health') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="good_health" value="0" {{ old('good_health') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                {{-- Q2 --}}
                <div class="col-12">
                    <label class="form-labelx">2. Are you under medical treatment now?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="under_treatment" value="1" {{ old('under_treatment') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="under_treatment" value="0" {{ old('under_treatment') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                    <div class="mt-2">
                        <input type="text" name="treatment_condition" class="inputx" placeholder="If yes, what is the condition being treated?" value="{{ old('treatment_condition') }}">
                    </div>
                </div>

                {{-- Q3 --}}
                <div class="col-12">
                    <label class="form-labelx">3. Have you ever had a serious illness or surgical operation?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="serious_illness" value="1" {{ old('serious_illness') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="serious_illness" value="0" {{ old('serious_illness') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                    <div class="mt-2">
                        <textarea name="serious_illness_details" rows="2" class="textareax" placeholder="If yes, please describe">{{ old('serious_illness_details') }}</textarea>
                    </div>
                </div>

                {{-- Q4 --}}
                <div class="col-12">
                    <label class="form-labelx">4. Have you ever been hospitalized?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="hospitalized" value="1" {{ old('hospitalized') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="hospitalized" value="0" {{ old('hospitalized') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                    <div class="mt-2">
                        <textarea name="hospitalized_reason" rows="2" class="textareax" placeholder="If yes, why?">{{ old('hospitalized_reason') }}</textarea>
                    </div>
                </div>

                {{-- Q5 --}}
                <div class="col-12">
                    <label class="form-labelx">5. Are you taking any prescription / non-prescription medication?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="taking_medication" value="1" {{ old('taking_medication') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="taking_medication" value="0" {{ old('taking_medication') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                    <div class="mt-2">
                        <textarea name="medications" rows="2" class="textareax" placeholder="If yes, list medications">{{ old('medications') }}</textarea>
                    </div>
                </div>

                {{-- Q6 --}}
                <div class="col-12">
                    <label class="form-labelx">6. Do you take aspirin regularly?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="takes_aspirin" value="1" {{ old('takes_aspirin') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="takes_aspirin" value="0" {{ old('takes_aspirin') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                {{-- Q7 allergies --}}
                <div class="col-12">
                    <label class="form-labelx">7. Do you have allergies? (check all that apply)</label>
                    <div class="checks-grid">
                        @foreach($allergyOptions as $opt)
                            <label class="check-item">
                                <input type="checkbox" name="allergies[]" value="{{ $opt }}" {{ in_array($opt, $oldAllergies) ? 'checked' : '' }}>
                                <div class="txt">{{ $opt }}</div>
                            </label>
                        @endforeach
                        <label class="check-item">
                            <input type="checkbox" value="Other" id="allergy_other_check">
                            <div class="txt">Other (specify)</div>
                        </label>
                    </div>
                    <div class="mt-2">
                        <input type="text" name="allergies_other" id="allergies_other" class="inputx" placeholder="Other allergy (optional)" value="{{ old('allergies_other') }}">
                    </div>
                </div>

                {{-- Habits --}}
                <div class="col-12 col-md-4">
                    <label class="form-labelx">8. Tobacco products?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="tobacco_use" value="1" {{ old('tobacco_use') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="tobacco_use" value="0" {{ old('tobacco_use') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">9. Alcohol?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="alcohol_use" value="1" {{ old('alcohol_use') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="alcohol_use" value="0" {{ old('alcohol_use') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">10. Dangerous drugs?</label>
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="dangerous_drugs" value="1" {{ old('dangerous_drugs') === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="dangerous_drugs" value="0" {{ old('dangerous_drugs') === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Bleeding Time</label>
                    <input type="text" name="bleeding_time" class="inputx" value="{{ old('bleeding_time') }}">
                </div>

                {{-- Female only --}}
                <div class="col-12 col-md-8">
                    <label class="form-labelx">For women only</label>
                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <div class="radio-row">
                                <label class="radio-pill"><input type="radio" name="pregnant" value="1" {{ old('pregnant') === '1' ? 'checked' : '' }}> Pregnant</label>
                                <label class="radio-pill"><input type="radio" name="pregnant" value="0" {{ old('pregnant') === '0' ? 'checked' : '' }}> No</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="radio-row">
                                <label class="radio-pill"><input type="radio" name="nursing" value="1" {{ old('nursing') === '1' ? 'checked' : '' }}> Nursing</label>
                                <label class="radio-pill"><input type="radio" name="nursing" value="0" {{ old('nursing') === '0' ? 'checked' : '' }}> No</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="radio-row">
                                <label class="radio-pill"><input type="radio" name="birth_control_pills" value="1" {{ old('birth_control_pills') === '1' ? 'checked' : '' }}> Birth control pills</label>
                                <label class="radio-pill"><input type="radio" name="birth_control_pills" value="0" {{ old('birth_control_pills') === '0' ? 'checked' : '' }}> No</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vitals --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Blood Type</label>
                    <input type="text" name="blood_type" class="inputx" value="{{ old('blood_type') }}">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Blood Pressure</label>
                    <input type="text" name="blood_pressure" class="inputx" value="{{ old('blood_pressure') }}">
                </div>

            </div>
        </div>
    </div>

    {{-- =======================
        CARD 5: MEDICAL CONDITIONS CHECKLIST
    ======================= --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title">
                <i class="fa fa-list-check"></i> Medical Conditions (check which apply)
            </div>
            <div class="hint">Optional, but recommended.</div>
        </div>

        <div class="card-bodyx">
            <div class="checks-grid">
                @foreach($medicalConditionOptions as $opt)
                    <label class="check-item">
                        <input type="checkbox" name="medical_conditions[]" value="{{ $opt }}" {{ in_array($opt, $oldConditions) ? 'checked' : '' }}>
                        <div class="txt">{{ $opt }}</div>
                    </label>
                @endforeach

                <label class="check-item">
                    <input type="checkbox" value="Other" id="cond_other_check">
                    <div class="txt">Other (specify)</div>
                </label>
            </div>

            <div class="mt-2">
                <input type="text" name="medical_conditions_other" id="medical_conditions_other" class="inputx" placeholder="Other condition (optional)" value="{{ old('medical_conditions_other') }}">
            </div>
        </div>
    </div>

    {{-- =======================
        CARD 6: SIGNATURE (Patient Info Record)
    ======================= --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title">
                <i class="fa fa-pen-fancy"></i> Patient Information Record Signature
                <span class="badge-mini">Required</span>
            </div>
            <div class="hint">Sign using finger/stylus on iPad.</div>
        </div>

        <div class="card-bodyx">
            <label class="form-labelx">Signature <span class="text-danger">*</span></label>
            <div class="sig-wrap">
                <canvas id="sig_patient_info" class="sig"></canvas>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear_sig_patient_info">Clear</button>
            </div>
            <div class="helper" id="sig_patient_info_err" style="color:#b91c1c; display:none;">
                Signature is required.
            </div>
        </div>
    </div>

    {{-- =======================
        CARD 7: INFORMED CONSENT
    ======================= --}}
    {{-- =======================
    CARD 7: INFORMED CONSENT
======================= --}}
<div class="card-shell">
    <div class="card-head">
        <div class="section-title">
            <i class="fa fa-file-signature"></i> Informed Consent
            <span class="badge-mini">Yes / No per section + sign</span>
        </div>
        <div class="hint">Checked = Yes, unchecked = No.</div>
    </div>

    <div class="card-bodyx">
        <div class="helper mb-2">
            Please review each section. Tick the box if the patient agrees to that section.
        </div>

        <div class="checks-grid" style="grid-template-columns: 1fr;">
            @foreach($consentSections as $key => $label)
                @php
                    $raw = $oldInitials[$key] ?? 'No';
                    $checked = in_array(strtolower((string)$raw), ['yes','1','true'], true);
                @endphp

                <label class="check-item" for="consent_{{ $key }}" style="align-items:center;">
                    {{-- default No --}}
                    <input type="hidden" name="consent_initials[{{ $key }}]" value="No">

                    {{-- checked = Yes --}}
                    <input
                        type="checkbox"
                        id="consent_{{ $key }}"
                        name="consent_initials[{{ $key }}]"
                        value="Yes"
                        {{ $checked ? 'checked' : '' }}
                    >

                    <div class="txt">
                        {{ $label }}
                        <div class="helper" style="margin-top:4px;">Yes = checked • No = unchecked</div>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="mt-3">
            <hr style="border-color:rgba(15,23,42,.10);">
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-labelx">Patient/Parent/Guardian Signature <span class="text-danger">*</span></label>
                <div class="sig-wrap">
                    <canvas id="sig_consent_patient" class="sig"></canvas>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clear_sig_consent_patient">Clear</button>
                </div>
                <div class="helper" id="sig_consent_patient_err" style="color:#b91c1c; display:none;">
                    Consent signature is required.
                </div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-labelx">Dentist Signature (optional now)</label>
                <div class="sig-wrap">
                    <canvas id="sig_consent_dentist" class="sig"></canvas>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clear_sig_consent_dentist">Clear</button>
                </div>
                <div class="helper">Staff can sign here, or add later in Edit.</div>
            </div>
        </div>
    </div>
</div>


    {{-- Submit --}}
    <div class="card-shell">
        <div class="card-bodyx">
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn-primaryx">
                    <i class="fa fa-check"></i> Save Patient Record
                </button>

                <a href="{{ route('staff.patients.index') }}" class="btn-ghostx">
                    <i class="fa fa-xmark"></i> Cancel
                </a>
            </div>
        </div>
    </div>

</form>

<script>
document.getElementById('forceCreateBtn')?.addEventListener('click', function () {
    document.getElementById('force_create').value = "1";
    document.getElementById('patientForm')?.submit();
});

// Minor toggle: guardian required only if is_minor checked
function syncMinor(){
    const minor = document.getElementById('is_minor');
    const req = !!minor?.checked;

    const gName = document.getElementById('guardian_name');
    const gOcc  = document.getElementById('guardian_occupation');

    const star1 = document.getElementById('guardian_name_star');
    const star2 = document.getElementById('guardian_occ_star');

    if (gName) gName.required = req;
    if (gOcc)  gOcc.required  = req;

    if (star1) star1.style.display = req ? 'inline' : 'none';
    if (star2) star2.style.display = req ? 'inline' : 'none';
}
document.getElementById('is_minor')?.addEventListener('change', syncMinor);
syncMinor();

// Signature pad setup
function setupSignature(canvasId, hiddenInputId, clearBtnId){
  const canvas = document.getElementById(canvasId);
  const hidden = document.getElementById(hiddenInputId);
  const clearBtn = document.getElementById(clearBtnId);
  const ctx = canvas.getContext('2d');

  function resize() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * ratio;
    canvas.height = rect.height * ratio;

    // reset transform to draw in CSS pixels
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
  }
  resize();
  window.addEventListener('resize', resize);

  let drawing = false;

  function pos(e){
    const r = canvas.getBoundingClientRect();
    return { x: e.clientX - r.left, y: e.clientY - r.top };
  }

  canvas.addEventListener('pointerdown', (e)=>{
    drawing = true;
    const p = pos(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
  });

  canvas.addEventListener('pointermove', (e)=>{
    if(!drawing) return;
    const p = pos(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
  });

  function stop(){ drawing = false; }
  canvas.addEventListener('pointerup', stop);
  canvas.addEventListener('pointercancel', stop);
  canvas.addEventListener('pointerleave', stop);

  clearBtn?.addEventListener('click', ()=>{
    ctx.clearRect(0,0,canvas.width,canvas.height);
    if (hidden) hidden.value = '';
  });

  function hasInk(){
    const img = ctx.getImageData(0,0,canvas.width,canvas.height).data;
    for (let i=3; i<img.length; i+=4){
      if (img[i] !== 0) return true; // alpha channel
    }
    return false;
  }

  function exportToHidden(){
    if (!hidden) return false;
    if (!hasInk()) {
      hidden.value = '';
      return false;
    }
    hidden.value = canvas.toDataURL('image/png');
    return true;
  }

  return { exportToHidden };
}

const sigPatientInfo = setupSignature('sig_patient_info', 'patient_info_signature', 'clear_sig_patient_info');
const sigConsentPatient = setupSignature('sig_consent_patient', 'consent_patient_signature', 'clear_sig_consent_patient');
const sigConsentDentist = setupSignature('sig_consent_dentist', 'consent_dentist_signature', 'clear_sig_consent_dentist');

document.getElementById('patientForm')?.addEventListener('submit', (e)=>{
    // export signatures into hidden fields
    const ok1 = sigPatientInfo.exportToHidden();
    const ok2 = sigConsentPatient.exportToHidden();
    sigConsentDentist.exportToHidden(); // optional

    // show inline errors
    const err1 = document.getElementById('sig_patient_info_err');
    const err2 = document.getElementById('sig_consent_patient_err');
    if (err1) err1.style.display = ok1 ? 'none' : 'block';
    if (err2) err2.style.display = ok2 ? 'none' : 'block';

    if (!ok1 || !ok2){
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>

@endsection
