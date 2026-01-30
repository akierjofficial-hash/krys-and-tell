@extends('layouts.staff')

@section('content')

<style>
/* ==========================================================
       Patients Edit (Dark mode compatible)
       - removes hardcoded #fff / #0f172a
       - uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2, --kt-border,
                           --kt-input-bg, --kt-input-border, --kt-shadow
       ========================================================== */

:root {
    --card-shadow: var(--kt-shadow);
    --card-border: 1px solid var(--kt-border);
    --soft: rgba(148, 163, 184, .14);

    --text: var(--kt-text);
    --muted: var(--kt-muted);
    --muted2: rgba(148, 163, 184, .72);

    --radius: 16px;
    --focus: rgba(96, 165, 250, .55);
    --focusRing: rgba(96, 165, 250, .18);
}

html[data-theme="dark"] {
    --soft: rgba(148, 163, 184, .16);
    --muted2: rgba(248, 250, 252, .60);
}

.page-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.page-title {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.3px;
    margin: 0;
    color: var(--text);
}

.subtitle {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: var(--muted);
}

.card-shell {
    background: var(--kt-surface);
    border: var(--card-border);
    border-radius: var(--radius);
    box-shadow: var(--card-shadow);
    overflow: hidden;
    width: 100%;
    margin-bottom: 14px;
    color: var(--text);
}

.card-head {
    padding: 16px 18px;
    border-bottom: 1px solid var(--soft);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    background: linear-gradient(180deg, rgba(148, 163, 184, .08), transparent);
}

html[data-theme="dark"] .card-head {
    background: linear-gradient(180deg, rgba(2, 6, 23, .45), rgba(17, 24, 39, 0));
}

.card-head .hint {
    font-size: 12px;
    color: var(--muted);
}

.card-bodyx {
    padding: 18px;
}

.form-labelx {
    font-weight: 800;
    font-size: 13px;
    color: rgba(148, 163, 184, .95);
    margin-bottom: 6px;
}

html[data-theme="dark"] .form-labelx {
    color: rgba(248, 250, 252, .78);
}

.inputx,
.selectx,
.textareax {
    width: 100%;
    border: 1px solid var(--kt-input-border);
    padding: 11px 12px;
    border-radius: 12px;
    font-size: 14px;
    color: var(--text);
    background: var(--kt-input-bg);
    outline: none;
    transition: .15s ease;
    box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
}

.inputx::placeholder,
.textareax::placeholder {
    color: rgba(148, 163, 184, .85);
}

html[data-theme="dark"] .inputx::placeholder,
html[data-theme="dark"] .textareax::placeholder {
    color: rgba(248, 250, 252, .52);
}

.inputx:focus,
.selectx:focus,
.textareax:focus {
    border-color: var(--focus);
    box-shadow: 0 0 0 4px var(--focusRing);
}

/* make select options readable in dark mode */
html[data-theme="dark"] .selectx,
html[data-theme="dark"] .selectx option {
    background-color: rgba(17, 24, 39, .98) !important;
    color: var(--kt-text) !important;
}

.helper {
    margin-top: 6px;
    font-size: 12px;
    color: var(--muted);
}

.btn-primaryx {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 14px;
    border-radius: 12px;
    font-weight: 900;
    font-size: 14px;
    border: none;
    color: #fff;
    text-decoration: none;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    box-shadow: 0 10px 18px rgba(34, 197, 94, .20);
    transition: .15s ease;
}

.btn-primaryx:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 24px rgba(34, 197, 94, .26);
    color: #fff;
}

.btn-ghostx {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 14px;
    border-radius: 12px;
    font-weight: 900;
    font-size: 14px;
    text-decoration: none;
    border: 1px solid var(--kt-border);
    color: var(--text);
    background: var(--kt-surface-2);
    transition: .15s ease;
}

.btn-ghostx:hover {
    background: rgba(148, 163, 184, .14);
    color: var(--text);
}

html[data-theme="dark"] .btn-ghostx:hover {
    background: rgba(17, 24, 39, .75);
}

.error-box {
    background: rgba(239, 68, 68, .12);
    border: 1px solid rgba(239, 68, 68, .28);
    color: #fecaca;
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 14px;
}

html:not([data-theme="dark"]) .error-box {
    color: #b91c1c;
}

.error-box .title {
    font-weight: 950;
    margin-bottom: 6px;
}

.error-box ul {
    margin: 0;
    padding-left: 18px;
    font-size: 13px;
}

.form-max {
    max-width: 1100px;
}

.section-title {
    font-size: 14px;
    font-weight: 950;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.radio-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 6px;
}

.radio-pill {
    border: 1px solid var(--kt-input-border);
    border-radius: 999px;
    padding: 8px 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--kt-input-bg);
    cursor: pointer;
    user-select: none;
    color: var(--text);
}

.checks-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

@media (max-width: 992px) {
    .checks-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 576px) {
    .checks-grid {
        grid-template-columns: 1fr;
    }
}

.check-item {
    border: 1px solid var(--kt-input-border);
    background: var(--kt-surface-2);
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    color: var(--text);
}

.check-item input {
    margin-top: 3px;
    transform: scale(1.15);
}

.check-item .txt {
    font-size: 13px;
    color: var(--text);
    font-weight: 800;
    line-height: 1.25;
}

/* Signature */
.sig-wrap {
    border: 1px solid var(--kt-input-border);
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    /* keep pad clear even in dark mode */
}

canvas.sig {
    width: 100%;
    height: 180px;
    display: block;
    touch-action: none;
}

.sig-preview {
    max-width: 520px;
    width: 100%;
    border: 1px solid var(--kt-border);
    border-radius: 12px;
    background: #fff;
    padding: 8px;
    margin-top: 10px;
}

/* consent hr */
.soft-hr {
    border-color: var(--soft) !important;
}
</style>

@php
$info = $patient->informationRecord;
$consent = $patient->informedConsent;

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

$consentSections = [
'treatment' => 'Treatment to be done (understand the plan + possible risks)',
'meds' => 'Drugs & medications (possible reactions / side effects)',
'changes' => 'Changes in treatment plan (may be required during procedure)',
'radiograph' => 'Radiographs / X-rays (limits + diagnostic value)',
'removal' => 'Removal of teeth (possible complications)',
'crowns' => 'Crowns / caps / bridges (risks and follow-ups)',
'endo' => 'Endodontics / root canal (risks and outcomes)',
'perio' => 'Periodontal disease (gum treatment options)',
'fillings' => 'Fillings (sensitivity, adjustments, future needs)',
'dentures' => 'Dentures (fit, soreness, adjustments)',
'disclaimer' => 'Acknowledgement (dentistry not exact science, consent granted)',
];

$oldAllergies = old('allergies', $info?->allergies ?? []);
$oldConditions = old('medical_conditions', $info?->medical_conditions ?? []);
$oldInitials = old('consent_initials', $consent?->initials ?? []);

$boolVal = function($value){
if ($value === true) return '1';
if ($value === false) return '0';
return '';
};

$isMinorChecked = old('is_minor', $info?->is_minor ? 1 : 0) ? true : false;
@endphp

<div class="page-head">
    <div>
        <h2 class="page-title">Edit Patient</h2>
        <p class="subtitle">Update the patient’s information record and consent.</p>
    </div>

    <x-back-button fallback="{{ route('staff.patients.show', $patient->id) }}" class="btn-ghostx"
        label="Back to Patient" />
</div>

@if ($errors->any())
<div class="error-box form-max">
    <div class="title"><i class="fa fa-triangle-exclamation"></i> Please fix the following:</div>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('staff.patients.update', $patient->id) }}" method="POST" id="patientEditForm" class="form-max">
    @csrf
    @method('PUT')

    {{-- hidden fields for signature canvases (only overwrite if user draws) --}}
    <input type="hidden" name="patient_info_signature" id="patient_info_signature">
    <input type="hidden" name="consent_patient_signature" id="consent_patient_signature">
    <input type="hidden" name="consent_dentist_signature" id="consent_dentist_signature">

    {{-- CARD 1: BASIC --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title"><i class="fa fa-user"></i> Patient Basic Info</div>
            <div class="hint">Editing: <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong></div>
        </div>

        <div class="card-bodyx">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-labelx">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="inputx"
                        value="{{ old('last_name', $patient->last_name) }}" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="inputx"
                        value="{{ old('first_name', $patient->first_name) }}" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Middle Name</label>
                    <input type="text" name="middle_name" class="inputx"
                        value="{{ old('middle_name', $patient->middle_name) }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Birthdate <span class="text-danger">*</span></label>
                    <input type="date" name="birthdate" class="inputx"
                        value="{{ old('birthdate', $patient->birthdate) }}" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Sex <span class="text-danger">*</span></label>
                    <select name="gender" class="selectx" required>
                        <option value="">-- Select --</option>
                        <option value="Male" {{ old('gender', $patient->gender) == 'Male' ? 'selected' : '' }}>Male
                        </option>
                        <option value="Female" {{ old('gender', $patient->gender) == 'Female' ? 'selected' : '' }}>
                            Female</option>
                        <option value="Other" {{ old('gender', $patient->gender) == 'Other' ? 'selected' : '' }}>Other
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Nickname</label>
                    <input type="text" name="nickname" class="inputx" value="{{ old('nickname', $info?->nickname) }}">
                    <div class="helper">Optional</div>
                </div>


                <div class="col-12">
                    <label class="form-labelx">Home Address <span class="text-danger">*</span></label>
                    <textarea name="address" rows="2" class="textareax"
                        required>{{ old('address', $patient->address) }}</textarea>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Occupation <span class="text-danger">*</span></label>
                    <input type="text" name="occupation" class="inputx"
                        value="{{ old('occupation', $info?->occupation) }}" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Cell/Mobile No. <span class="text-danger">*</span></label>
                    <input type="text" name="contact_number" class="inputx"
                        value="{{ old('contact_number', $patient->contact_number) }}" required>
                    <div class="helper">Example: 09XXXXXXXXX</div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Email Add <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="inputx" value="{{ old('email', $patient->email) }}"
                        required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Home No.</label>
                    <input type="text" name="home_no" class="inputx" value="{{ old('home_no', $info?->home_no) }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Office No.</label>
                    <input type="text" name="office_no" class="inputx"
                        value="{{ old('office_no', $info?->office_no) }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Fax No.</label>
                    <input type="text" name="fax_no" class="inputx" value="{{ old('fax_no', $info?->fax_no) }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Dental Insurance</label>
                    <input type="text" name="dental_insurance" class="inputx"
                        value="{{ old('dental_insurance', $info?->dental_insurance) }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Effective Date</label>
                    <input type="date" name="effective_date" class="inputx"
                        value="{{ old('effective_date', optional($info?->effective_date)->format('Y-m-d')) }}">
                </div>

                <div class="col-12">
                    <label class="form-labelx">Notes (Internal)</label>
                    <textarea name="notes" rows="3" class="textareax">{{ old('notes', $patient->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 2: MINOR + REFERRAL --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title"><i class="fa fa-id-card"></i> For Minors / Referral / Reason</div>
            <div class="hint">Guardian is required only if minor is checked.</div>
        </div>

        <div class="card-bodyx">
            <div class="row g-3">
                <div class="col-12">
                    <label class="radio-pill" style="width:100%; justify-content:space-between;">
                        <span style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="is_minor" id="is_minor" value="1"
                                {{ $isMinorChecked ? 'checked' : '' }}>
                            <span style="font-weight:950;">Patient is a Minor</span>
                        </span>
                    </label>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Parent/Guardian’s Name <span class="text-danger"
                            id="guardian_name_star">*</span></label>
                    <input type="text" name="guardian_name" id="guardian_name" class="inputx"
                        value="{{ old('guardian_name', $info?->guardian_name) }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Occupation <span class="text-danger"
                            id="guardian_occ_star">*</span></label>
                    <input type="text" name="guardian_occupation" id="guardian_occupation" class="inputx"
                        value="{{ old('guardian_occupation', $info?->guardian_occupation) }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Whom may we thank for referring you?</label>
                    <input type="text" name="referral_source" class="inputx"
                        value="{{ old('referral_source', $info?->referral_source) }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Reason for dental consultation?</label>
                    <input type="text" name="consultation_reason" class="inputx"
                        value="{{ old('consultation_reason', $info?->consultation_reason) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 3: DENTAL HISTORY --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title"><i class="fa fa-tooth"></i> Dental History</div>
            <div class="hint">Optional</div>
        </div>

        <div class="card-bodyx">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Previous Dentist / Dr.</label>
                    <input type="text" name="previous_dentist" class="inputx"
                        value="{{ old('previous_dentist', $info?->previous_dentist) }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Last Dental Visit</label>
                    <input type="date" name="last_dental_visit" class="inputx"
                        value="{{ old('last_dental_visit', optional($info?->last_dental_visit)->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 4: MEDICAL HISTORY --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title"><i class="fa fa-notes-medical"></i> Medical History</div>
            <div class="hint">Yes/No where applicable</div>
        </div>

        <div class="card-bodyx">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Name of Physician</label>
                    <input type="text" name="physician_name" class="inputx"
                        value="{{ old('physician_name', $info?->physician_name) }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Specialty</label>
                    <input type="text" name="physician_specialty" class="inputx"
                        value="{{ old('physician_specialty', $info?->physician_specialty) }}">
                </div>

                <div class="col-12">
                    <label class="form-labelx">1. Are you in good health?</label>
                    @php $gh = old('good_health', $boolVal($info?->good_health)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="good_health" value="1"
                                {{ $gh === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="good_health" value="0"
                                {{ $gh === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-labelx">2. Are you under medical treatment now?</label>
                    @php $ut = old('under_treatment', $boolVal($info?->under_treatment)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="under_treatment" value="1"
                                {{ $ut === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="under_treatment" value="0"
                                {{ $ut === '0' ? 'checked' : '' }}> No</label>
                    </div>
                    <div class="mt-2">
                        <input type="text" name="treatment_condition" class="inputx"
                            placeholder="If yes, what condition?"
                            value="{{ old('treatment_condition', $info?->treatment_condition) }}">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-labelx">3. Serious illness or surgical operation?</label>
                    @php $si = old('serious_illness', $boolVal($info?->serious_illness)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="serious_illness" value="1"
                                {{ $si === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="serious_illness" value="0"
                                {{ $si === '0' ? 'checked' : '' }}> No</label>
                    </div>
                    <div class="mt-2">
                        <textarea name="serious_illness_details" rows="2" class="textareax"
                            placeholder="If yes, describe">{{ old('serious_illness_details', $info?->serious_illness_details) }}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-labelx">4. Have you ever been hospitalized?</label>
                    @php $hos = old('hospitalized', $boolVal($info?->hospitalized)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="hospitalized" value="1"
                                {{ $hos === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="hospitalized" value="0"
                                {{ $hos === '0' ? 'checked' : '' }}> No</label>
                    </div>
                    <div class="mt-2">
                        <textarea name="hospitalized_reason" rows="2" class="textareax"
                            placeholder="If yes, why?">{{ old('hospitalized_reason', $info?->hospitalized_reason) }}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-labelx">5. Are you taking any medication?</label>
                    @php $tm = old('taking_medication', $boolVal($info?->taking_medication)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="taking_medication" value="1"
                                {{ $tm === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="taking_medication" value="0"
                                {{ $tm === '0' ? 'checked' : '' }}> No</label>
                    </div>
                    <div class="mt-2">
                        <textarea name="medications" rows="2" class="textareax"
                            placeholder="If yes, list">{{ old('medications', $info?->medications) }}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-labelx">6. Do you take aspirin regularly?</label>
                    @php $ta = old('takes_aspirin', $boolVal($info?->takes_aspirin)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="takes_aspirin" value="1"
                                {{ $ta === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="takes_aspirin" value="0"
                                {{ $ta === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-labelx">7. Allergies (check all that apply)</label>
                    <div class="checks-grid">
                        @foreach($allergyOptions as $opt)
                        <label class="check-item">
                            <input type="checkbox" name="allergies[]" value="{{ $opt }}"
                                {{ in_array($opt, $oldAllergies) ? 'checked' : '' }}>
                            <div class="txt">{{ $opt }}</div>
                        </label>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <input type="text" name="allergies_other" class="inputx" placeholder="Other allergy (optional)"
                            value="{{ old('allergies_other', $info?->allergies_other) }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">8. Tobacco products?</label>
                    @php $tb = old('tobacco_use', $boolVal($info?->tobacco_use)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="tobacco_use" value="1"
                                {{ $tb === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="tobacco_use" value="0"
                                {{ $tb === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">9. Alcohol?</label>
                    @php $al = old('alcohol_use', $boolVal($info?->alcohol_use)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="alcohol_use" value="1"
                                {{ $al === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="alcohol_use" value="0"
                                {{ $al === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">10. Dangerous drugs?</label>
                    @php $dd = old('dangerous_drugs', $boolVal($info?->dangerous_drugs)); @endphp
                    <div class="radio-row">
                        <label class="radio-pill"><input type="radio" name="dangerous_drugs" value="1"
                                {{ $dd === '1' ? 'checked' : '' }}> Yes</label>
                        <label class="radio-pill"><input type="radio" name="dangerous_drugs" value="0"
                                {{ $dd === '0' ? 'checked' : '' }}> No</label>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Bleeding Time</label>
                    <input type="text" name="bleeding_time" class="inputx"
                        value="{{ old('bleeding_time', $info?->bleeding_time) }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Blood Type</label>
                    <input type="text" name="blood_type" class="inputx"
                        value="{{ old('blood_type', $info?->blood_type) }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-labelx">Blood Pressure</label>
                    <input type="text" name="blood_pressure" class="inputx"
                        value="{{ old('blood_pressure', $info?->blood_pressure) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 5: CONDITIONS --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title"><i class="fa fa-list-check"></i> Medical Conditions</div>
            <div class="hint">Checkboxes</div>
        </div>

        <div class="card-bodyx">
            <div class="checks-grid">
                @foreach($medicalConditionOptions as $opt)
                <label class="check-item">
                    <input type="checkbox" name="medical_conditions[]" value="{{ $opt }}"
                        {{ in_array($opt, $oldConditions) ? 'checked' : '' }}>
                    <div class="txt">{{ $opt }}</div>
                </label>
                @endforeach
            </div>
            <div class="mt-2">
                <input type="text" name="medical_conditions_other" class="inputx"
                    placeholder="Other condition (optional)"
                    value="{{ old('medical_conditions_other', $info?->medical_conditions_other) }}">
            </div>
        </div>
    </div>

    {{-- CARD 6: PATIENT INFO SIGNATURE --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title"><i class="fa fa-pen-fancy"></i> Patient Info Signature</div>
            <div class="hint">Draw to replace existing signature (optional)</div>
        </div>

        <div class="card-bodyx">
            <label class="form-labelx">New Signature (optional)</label>
            <div class="sig-wrap">
                <canvas id="sig_patient_info" class="sig"></canvas>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary"
                    id="clear_sig_patient_info">Clear</button>
            </div>

            @if($info && $info->signature_path)
            <div class="helper">Current saved signature:</div>
            <img class="sig-preview" src="{{ asset('storage/'.$info->signature_path) }}"
                alt="Current Patient Signature">
            @endif
        </div>
    </div>

    {{-- CARD 7: INFORMED CONSENT --}}
    <div class="card-shell">
        <div class="card-head">
            <div class="section-title"><i class="fa fa-file-signature"></i> Informed Consent</div>
            <div class="hint">Yes / No per section + optionally re-sign</div>
        </div>

        <div class="card-bodyx">
            <div class="helper mb-2">
                Checked = Yes, unchecked = No. (Old saved “initials” will be treated as Yes.)
            </div>

            <div class="checks-grid" style="grid-template-columns: 1fr;">
                @foreach($consentSections as $key => $label)
                @php
                $raw = $oldInitials[$key] ?? null;
                $rawLower = strtolower((string)$raw);
                $checked = !empty($raw) && !in_array($rawLower, ['no','0','false'], true);
                @endphp

                <label class="check-item" for="consent_{{ $key }}" style="align-items:center;">
                    <input type="hidden" name="consent_initials[{{ $key }}]" value="No">
                    <input type="checkbox" id="consent_{{ $key }}" name="consent_initials[{{ $key }}]" value="Yes"
                        {{ $checked ? 'checked' : '' }}>

                    <div class="txt">
                        {{ $label }}
                        <div class="helper" style="margin-top:4px;">Yes = checked • No = unchecked</div>
                    </div>
                </label>
                @endforeach
            </div>

            <div class="mt-3">
                <hr class="soft-hr">
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-labelx">New Patient/Guardian Signature (optional)</label>
                    <div class="sig-wrap">
                        <canvas id="sig_consent_patient" class="sig"></canvas>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            id="clear_sig_consent_patient">Clear</button>
                    </div>

                    @if($consent && $consent->patient_signature_path)
                    <div class="helper">Current saved signature:</div>
                    <img class="sig-preview" src="{{ asset('storage/'.$consent->patient_signature_path) }}"
                        alt="Current Consent Patient Signature">
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">New Dentist Signature (optional)</label>
                    <div class="sig-wrap">
                        <canvas id="sig_consent_dentist" class="sig"></canvas>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            id="clear_sig_consent_dentist">Clear</button>
                    </div>

                    @if($consent && $consent->dentist_signature_path)
                    <div class="helper">Current saved signature:</div>
                    <img class="sig-preview" src="{{ asset('storage/'.$consent->dentist_signature_path) }}"
                        alt="Current Consent Dentist Signature">
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- SUBMIT --}}
    <div class="card-shell">
        <div class="card-bodyx">
            <div class="d-flex gap-2 flex-wrap pt-2">
                <button type="submit" class="btn-primaryx">
                    <i class="fa fa-check"></i> Update Patient Record
                </button>

                <a href="{{ route('staff.patients.show', $patient->id) }}" class="btn-ghostx">
                    <i class="fa fa-xmark"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
// Minor toggle: guardian required only if is_minor checked
function syncMinor() {
    const minor = document.getElementById('is_minor');
    const req = !!minor?.checked;

    const gName = document.getElementById('guardian_name');
    const gOcc = document.getElementById('guardian_occupation');

    const star1 = document.getElementById('guardian_name_star');
    const star2 = document.getElementById('guardian_occ_star');

    if (gName) gName.required = req;
    if (gOcc) gOcc.required = req;

    if (star1) star1.style.display = req ? 'inline' : 'none';
    if (star2) star2.style.display = req ? 'inline' : 'none';
}
document.getElementById('is_minor')?.addEventListener('change', syncMinor);
syncMinor();

// Signature pad setup
function setupSignature(canvasId, hiddenInputId, clearBtnId) {
    const canvas = document.getElementById(canvasId);
    const hidden = document.getElementById(hiddenInputId);
    const clearBtn = document.getElementById(clearBtnId);
    const ctx = canvas.getContext('2d');

    function resize() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;

        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
    }
    resize();
    window.addEventListener('resize', resize);

    let drawing = false;

    function pos(e) {
        const r = canvas.getBoundingClientRect();
        return {
            x: e.clientX - r.left,
            y: e.clientY - r.top
        };
    }

    canvas.addEventListener('pointerdown', (e) => {
        drawing = true;
        const p = pos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
    });

    canvas.addEventListener('pointermove', (e) => {
        if (!drawing) return;
        const p = pos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
    });

    function stop() {
        drawing = false;
    }
    canvas.addEventListener('pointerup', stop);
    canvas.addEventListener('pointercancel', stop);
    canvas.addEventListener('pointerleave', stop);

    clearBtn?.addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (hidden) hidden.value = '';
    });

    function hasInk() {
        const img = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        for (let i = 3; i < img.length; i += 4) {
            if (img[i] !== 0) return true;
        }
        return false;
    }

    function exportToHidden() {
        if (!hidden) return;
        hidden.value = hasInk() ? canvas.toDataURL('image/png') : '';
    }

    return {
        exportToHidden
    };
}

const sigPatientInfo = setupSignature('sig_patient_info', 'patient_info_signature', 'clear_sig_patient_info');
const sigConsentPatient = setupSignature('sig_consent_patient', 'consent_patient_signature',
    'clear_sig_consent_patient');
const sigConsentDentist = setupSignature('sig_consent_dentist', 'consent_dentist_signature',
    'clear_sig_consent_dentist');

document.getElementById('patientEditForm')?.addEventListener('submit', () => {
    sigPatientInfo.exportToHidden();
    sigConsentPatient.exportToHidden();
    sigConsentDentist.exportToHidden();
});
</script>

@endsection