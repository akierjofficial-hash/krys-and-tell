{{-- resources/views/staff/patients/print/patient_information.blade.php --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Information Record</title>
    <style>
        @page {
            size: letter;
            margin: 0.35in 0.45in 0.35in 0.45in;
        }

        * { box-sizing: border-box; }
        body{
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color:#000;
            margin:0;
            padding:0;
        }

        /* ===== PAGE WRAPPER ===== */
        .page{
            width: 100%;
            page-break-after: always;
        }
        .page:last-child{ page-break-after: auto; }

        /* ===== HEADER (match image vibe) ===== */
        .hdr{
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .hdr td{ vertical-align: top; }

        .logo-wrap{
            width: 28%;
            padding-top: 2px;
        }
        .logo{
            width: 150px;
            height: auto;
        }

        .center-wrap{
            width: 44%;
            text-align: left;
            padding-top: 6px;
        }
        .clinic-name{
            font-weight: 900;
            font-size: 13px;
            letter-spacing: .2px;
        }
        .clinic-line{
            margin-top: 2px;
            font-size: 10px;
        }

        .chart-wrap{
            width: 28%;
            text-align: right;
            padding-top: 2px;
        }
        .chart-title{
            display: inline-block;
            background: #000;
            color:#fff;
            font-weight: 900;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: .3px;
        }
        .chart-box{
            margin-top: 6px;
            width: 160px;
            height: 78px;
            border: 1px solid #000;
            border-radius: 16px;
            display: inline-block;
        }

        /* ===== SECTION TITLES ===== */
        .section-title{
            font-weight: 900;
            font-size: 12px;
            margin: 6px 0 4px 0;
        }
        .rule{
            border-top: 1px solid #000;
            margin: 2px 0 6px 0;
        }

        /* ===== FORM LINES ===== */
        .row-table{ width: 100%; border-collapse: collapse; }
        .row-table td{ padding: 0; }

        .lbl{
            white-space: nowrap;
            padding-right: 6px;
        }
        .req{
            color: #cc0000;
            font-weight: 900;
            padding-left: 2px;
        }
        .line{
            border-bottom: 1px solid #000;
            height: 16px;
            vertical-align: bottom;
        }
        .val{
            display: inline-block;
            padding: 0 2px 1px 2px;
            font-weight: 700;
            font-size: 11px;
        }
        .subcap{
            font-size: 9px;
            color:#000;
            padding-top: 2px;
        }

        /* main 2-col block like the image */
        .two-col{
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        .two-col td{
            vertical-align: top;
            padding-right: 12px;
        }
        .two-col td:last-child{ padding-right: 0; }
        .left-col{ width: 62%; }
        .right-col{ width: 38%; }

        .spacer-6{ height: 6px; }
        .spacer-10{ height: 10px; }

        /* ===== LIST STYLE (medical questions) ===== */
        .q{
            font-size: 10px;
            line-height: 1.25;
            margin: 1px 0;
        }
        .ans{
            font-weight: 800;
        }

        /* ===== CHECKLIST (3 cols like image) ===== */
        .check3{
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        .check3 td{
            width: 33.333%;
            vertical-align: top;
            font-size: 10px;
            line-height: 1.25;
            padding-right: 8px;
        }
        .box{
            display:inline-block;
            width: 14px;
            text-align: center;
            font-weight: 900;
        }

        /* ===== SIGNATURE AREA (DOMPDF SAFE ALIGN) ===== */
        .sig-area{
            width: 100%;
            margin-top: 10px;
        }
        .sig-table{
            width: 100%;
            border-collapse: collapse;
        }
        .sig-cell{
            text-align: right; /* ✅ DOMPDF reliable */
        }
        .sig-box{
            width: 280px;          /* same width as the line */
            height: 70px;          /* room for image above */
            position: relative;
            display: inline-block; /* keep tight */
        }
        .sig-box img{
            position: absolute;
            left: 0;
            right: 0;
            bottom: 22px;          /* sits above the line */
            margin: 0 auto;
            max-width: 260px;
            max-height: 42px;
            width: auto;
            height: auto;
            display: block;
        }
        .sig-line{
            position: absolute;
            left: 0;
            right: 0;
            bottom: 18px;
            border-bottom: 1px solid #000;
            height: 0;
        }
        .sig-text{
            position: absolute;
            right: 0;
            bottom: 0;
            font-weight: 700;
            font-style: italic;
            font-size: 11px;
        }

        /* ===== PAGE 2 CONSENT SUMMARY ===== */
        .consent-table{
            width:100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 10.5px;
        }
        .consent-table th, .consent-table td{
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        .consent-table th{
            background: #f2f2f2;
            font-weight: 900;
            text-align: left;
        }
        .yn{
            white-space: nowrap;
            font-weight: 800;
        }
        .sig2{
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }
        .sig2 td{
            width:50%;
            vertical-align: top;
            padding-right: 10px;
        }
        .sig2 td:last-child{ padding-right: 0; }
        .sig-cap{
            margin-top: 4px;
            font-size: 10px;
            font-weight: 800;
        }
        .sig-prev{
            width: 280px;
            height: auto;
            border: 1px solid #000;
            padding: 6px;
        }
    </style>
</head>
<body>
@php
    // Expecting $patient passed from controller
    $info = $patient->informationRecord ?? null;
    $consent = $patient->informedConsent ?? null;

    $logoPath = public_path('images/katlogo.jpeg');
    $logoData = file_exists($logoPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    $fmtDate = function($d, $format = 'm/d/Y'){
        if (!$d) return '';
        try { return \Carbon\Carbon::parse($d)->format($format); } catch (\Throwable $e) { return ''; }
    };

    $age = '';
    if (!empty($patient->birthdate)) {
        try { $age = \Carbon\Carbon::parse($patient->birthdate)->age; } catch (\Throwable $e) { $age=''; }
    }

    $arr = fn($v) => is_array($v) ? $v : (empty($v) ? [] : (array)$v);

    $yesNo = function($value){
        // Supports old initials (non-empty) OR new Yes/No storage
        if ($value === 'Yes' || $value === 'No') return $value;
        if (is_string($value) && trim($value) !== '') return 'Yes';
        return '';
    };

    $allergies = $arr($info->allergies ?? []);
    $conditions = $arr($info->medical_conditions ?? []);

    // Condition ordering to match the photo layout as close as possible
    $col1 = [
        'High Blood Pressure',
        'Low Blood Pressure',
        'Epilepsy / Convulsions',
        'AIDS or HIV Infection',
        'Sexually Transmitted Disease',
        'Stomach Troubles / Ulcers',
        'Fainting Seizure',
        'Rapid Weight Loss',
        'Radiation Therapy',
        'Joint Replacement / Implant',
        'Heart Surgery',
        'Heart Attack',
        'Thyroid Problem',
    ];
    $col2 = [
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
    ];
    $col3 = [
        'Cancer',
        'Anemia',
        'Angina',
        'Asthma',
        'Emphysema',
        'Bleeding Problems',
        'Blood Diseases',
        'Head Injuries',
        'Arthritis / Rheumatism',
        'Other',
    ];

    $isChecked = fn($needle) => in_array($needle, $conditions, true);

    $sigInfo = ($info && $info->signature_path) ? public_path('storage/'.$info->signature_path) : null;
    $sigConsentPatient = ($consent && $consent->patient_signature_path) ? public_path('storage/'.$consent->patient_signature_path) : null;
    $sigConsentDentist = ($consent && $consent->dentist_signature_path) ? public_path('storage/'.$consent->dentist_signature_path) : null;

    $imgData = function($path){
        if (!$path || !file_exists($path)) return null;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = $ext === 'jpg' ? 'jpeg' : $ext;
        return 'data:image/'.$mime.';base64,'.base64_encode(file_get_contents($path));
    };

    $sigInfoData = $imgData($sigInfo);
    $sigConsentPatientData = $imgData($sigConsentPatient);
    $sigConsentDentistData = $imgData($sigConsentDentist);

    $consentSections = [
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

{{-- =========================
    PAGE 1 (Patient Info Record)
========================= --}}
<div class="page">
    <table class="hdr">
        <tr>
            <td class="logo-wrap">
                @if($logoData)
                    <img class="logo" src="{{ $logoData }}" alt="Logo">
                @endif
            </td>

            <td class="center-wrap">
                <div class="clinic-name">KRYS&amp;TELL DENTAL CENTER</div>
                <div class="clinic-line">CT Building Jose Romeo Road, Bagacay Dumaguete City</div>
                <div class="clinic-line">09772443595</div>
                <div class="clinic-line">Krys&amp;Tell Dental Center</div>
            </td>

            <td class="chart-wrap">
                <div class="chart-title">DENTAL CHART</div>
                <div class="chart-box"></div>
            </td>
        </tr>
    </table>

    <div class="section-title">PATIENT INFORMATION RECORD</div>

    {{-- NAME LINE (Last/First/Middle) --}}
    <table class="row-table">
        <tr>
            <td class="lbl">Name:<span class="req">✓</span></td>
            <td style="width:100%;">
                <table class="row-table" style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td class="line" style="width:33.33%; padding-right:8px;">
                            <span class="val">{{ $patient->last_name ?? '' }}</span>
                        </td>
                        <td class="line" style="width:33.33%; padding-right:8px;">
                            <span class="val">{{ $patient->first_name ?? '' }}</span>
                        </td>
                        <td class="line" style="width:33.33%;">
                            <span class="val">{{ $patient->middle_name ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="subcap">Last Name</td>
                        <td class="subcap">First Name</td>
                        <td class="subcap">Middle Name</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="spacer-6"></div>

    {{-- TWO COLUMN BLOCK (like the image) --}}
    <table class="two-col">
        <tr>
            {{-- LEFT --}}
            <td class="left-col">
                <table class="row-table">
                    <tr>
                        <td class="lbl">Home Address:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $patient->address ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Birthdate (mm/dd/yyyy):<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $fmtDate($patient->birthdate) }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Occupation:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $info->occupation ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Dental Insurance:</td>
                        <td class="line"><span class="val">{{ $info->dental_insurance ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Effective Date:</td>
                        <td class="line"><span class="val">{{ $fmtDate($info->effective_date ?? null) }}</span></td>
                    </tr>
                </table>

                <div class="spacer-10"></div>

                <div style="font-weight:900;">For Minors:</div>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Parent/ Guardian’s Name:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $info->guardian_name ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Occupation:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $info->guardian_occupation ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Whom may we thank for referring you?</td>
                        <td class="line"><span class="val">{{ $info->referral_source ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">What is your reason for dental consultation?</td>
                        <td class="line"><span class="val">{{ $info->consultation_reason ?? '' }}</span></td>
                    </tr>
                </table>
            </td>

            {{-- RIGHT --}}
            <td class="right-col">
                <table class="row-table">
                    <tr>
                        <td class="lbl">Sex:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $patient->gender ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Age:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $age }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Nickname:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $info->nickname ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Home No.:</td>
                        <td class="line"><span class="val">{{ $info->home_no ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Office No.:</td>
                        <td class="line"><span class="val">{{ $info->office_no ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Fax No.:</td>
                        <td class="line"><span class="val">{{ $info->fax_no ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Cell/ Mobile No.:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $patient->contact_number ?? '' }}</span></td>
                    </tr>
                </table>

                <div class="spacer-6"></div>

                <table class="row-table">
                    <tr>
                        <td class="lbl">Email Add:<span class="req">✓</span></td>
                        <td class="line"><span class="val">{{ $patient->email ?? '' }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="spacer-10"></div>

    {{-- DENTAL HISTORY --}}
    <div class="section-title">DENTAL HISTORY</div>
    <div class="rule"></div>

    <table class="two-col">
        <tr>
            <td class="left-col">
                <table class="row-table">
                    <tr>
                        <td class="lbl">Previous Dentist Dr.</td>
                        <td class="line"><span class="val">{{ $info->previous_dentist ?? '' }}</span></td>
                    </tr>
                </table>
            </td>
            <td class="right-col">
                <table class="row-table">
                    <tr>
                        <td class="lbl">Last Dental Visit:</td>
                        <td class="line"><span class="val">{{ $fmtDate($info->last_dental_visit ?? null) }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="spacer-10"></div>

    {{-- MEDICAL HISTORY --}}
    <div class="section-title">MEDICAL HISTORY</div>
    <div class="rule"></div>

    <table class="two-col">
        <tr>
            <td class="left-col">
                <table class="row-table">
                    <tr>
                        <td class="lbl">Name of Physician:</td>
                        <td class="line"><span class="val">{{ $info->physician_name ?? '' }}</span></td>
                    </tr>
                </table>
            </td>
            <td class="right-col">
                <table class="row-table">
                    <tr>
                        <td class="lbl">Specialty (if applicable):</td>
                        <td class="line"><span class="val">{{ $info->physician_specialty ?? '' }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="spacer-6"></div>

    {{-- Questions (kept compact like the form) --}}
    <div class="q">1. Are you in good health? <span class="ans">{{ ($info?->good_health === 1 || $info?->good_health === true) ? 'Yes' : (($info?->good_health === 0 || $info?->good_health === false) ? 'No' : '') }}</span></div>
    <div class="q">2. Are you under medical treatment now? <span class="ans">{{ ($info?->under_treatment === 1 || $info?->under_treatment === true) ? 'Yes' : (($info?->under_treatment === 0 || $info?->under_treatment === false) ? 'No' : '') }}</span></div>
    <div class="q">&nbsp;&nbsp;&nbsp;&nbsp;a. If so, what is the condition being treated? <span class="ans">{{ $info?->treatment_condition ?? '' }}</span></div>
    <div class="q">3. Have you ever had serious illness or surgical operation? <span class="ans">{{ ($info?->serious_illness === 1 || $info?->serious_illness === true) ? 'Yes' : (($info?->serious_illness === 0 || $info?->serious_illness === false) ? 'No' : '') }}</span></div>
    <div class="q">&nbsp;&nbsp;&nbsp;&nbsp;a. If so, what illness or operation? <span class="ans">{{ $info?->serious_illness_details ?? '' }}</span></div>
    <div class="q">4. Have you ever been hospitalized? <span class="ans">{{ ($info?->hospitalized === 1 || $info?->hospitalized === true) ? 'Yes' : (($info?->hospitalized === 0 || $info?->hospitalized === false) ? 'No' : '') }}</span></div>
    <div class="q">&nbsp;&nbsp;&nbsp;&nbsp;a. If so, please specify <span class="ans">{{ $info?->hospitalized_reason ?? '' }}</span></div>
    <div class="q">5. Are you taking any prescription/nonprescription medication? <span class="ans">{{ ($info?->taking_medication === 1 || $info?->taking_medication === true) ? 'Yes' : (($info?->taking_medication === 0 || $info?->taking_medication === false) ? 'No' : '') }}</span></div>
    <div class="q">&nbsp;&nbsp;&nbsp;&nbsp;a. If so, please specify <span class="ans">{{ $info?->medications ?? '' }}</span></div>
    <div class="q">6. Do you take aspirin regularly? <span class="ans">{{ ($info?->takes_aspirin === 1 || $info?->takes_aspirin === true) ? 'Yes' : (($info?->takes_aspirin === 0 || $info?->takes_aspirin === false) ? 'No' : '') }}</span></div>

    <div class="q">7. Do you have allergies to any of the following?</div>
    <div class="q">
        <span class="box">[{{ in_array('Local Anesthetic', $allergies, true) ? 'x' : ' ' }}]</span> Local Anesthetic&nbsp;&nbsp;
        <span class="box">[{{ in_array('Penicillin', $allergies, true) ? 'x' : ' ' }}]</span> Penicillin&nbsp;&nbsp;
        <span class="box">[{{ in_array('Sulfa Drugs', $allergies, true) ? 'x' : ' ' }}]</span> Sulfa Drugs&nbsp;&nbsp;
        <span class="box">[{{ in_array('Aspirin', $allergies, true) ? 'x' : ' ' }}]</span> Aspirin&nbsp;&nbsp;
        <span class="box">[{{ in_array('Latex', $allergies, true) ? 'x' : ' ' }}]</span> Latex
        @if(!empty($info?->allergies_other))
            &nbsp;&nbsp;Other: <span class="ans">{{ $info->allergies_other }}</span>
        @endif
    </div>

    <div class="q">8. Do you use tobacco products? <span class="ans">{{ ($info?->tobacco_use === 1 || $info?->tobacco_use === true) ? 'Yes' : (($info?->tobacco_use === 0 || $info?->tobacco_use === false) ? 'No' : '') }}</span></div>
    <div class="q">9. Do you use alcohol, cocaine or other dangerous drugs? <span class="ans">
        {{ (($info?->alcohol_use === 1 || $info?->alcohol_use === true) || ($info?->dangerous_drugs === 1 || $info?->dangerous_drugs === true)) ? 'Yes' : ((($info?->alcohol_use === 0 || $info?->alcohol_use === false) && ($info?->dangerous_drugs === 0 || $info?->dangerous_drugs === false)) ? 'No' : '') }}
    </span></div>
    <div class="q">10. Bleeding Time <span class="ans">{{ $info?->bleeding_time ?? '' }}</span></div>

    <div class="q" style="margin-top:2px;">
        For women only:
        Are you pregnant? <span class="ans">{{ ($info?->pregnant === 1 || $info?->pregnant === true) ? 'Yes' : (($info?->pregnant === 0 || $info?->pregnant === false) ? 'No' : '') }}</span>
        &nbsp;&nbsp;Are you nursing? <span class="ans">{{ ($info?->nursing === 1 || $info?->nursing === true) ? 'Yes' : (($info?->nursing === 0 || $info?->nursing === false) ? 'No' : '') }}</span>
        &nbsp;&nbsp;Are you taking birth control pills? <span class="ans">{{ ($info?->birth_control_pills === 1 || $info?->birth_control_pills === true) ? 'Yes' : (($info?->birth_control_pills === 0 || $info?->birth_control_pills === false) ? 'No' : '') }}</span>
    </div>

    <div class="spacer-6"></div>

    <div class="q">11. Blood Type <span class="ans">{{ $info?->blood_type ?? '' }}</span></div>
    <div class="q">12. Blood Pressure <span class="ans">{{ $info?->blood_pressure ?? '' }}</span></div>

    <div class="q" style="margin-top:4px;">
        13. Do you have or have you had any of the following? Check which apply.
    </div>

    <table class="check3">
        <tr>
            <td>
                @foreach($col1 as $opt)
                    <div><span class="box">[{{ $isChecked($opt) ? 'x' : ' ' }}]</span> {{ $opt }}</div>
                @endforeach
            </td>
            <td>
                @foreach($col2 as $opt)
                    <div><span class="box">[{{ $isChecked($opt) ? 'x' : ' ' }}]</span> {{ $opt }}</div>
                @endforeach
            </td>
            <td>
                @foreach($col3 as $opt)
                    <div><span class="box">[{{ $isChecked($opt) ? 'x' : ' ' }}]</span> {{ $opt }}</div>
                @endforeach
                @if(!empty($info?->medical_conditions_other))
                    <div style="margin-top:2px;"><span class="ans">Other:</span> {{ $info->medical_conditions_other }}</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- SIGNATURE (bottom like image) --}}
    <div class="sig-area">
        <table class="sig-table">
            <tr>
                <td class="sig-cell">
                    <div class="sig-box">
                        @if($sigInfoData)
                            <img src="{{ $sigInfoData }}" alt="Signature">
                        @endif

                        <div class="sig-line"></div>
                        <div class="sig-text">Signature</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- =========================
    PAGE 2 (Consent summary)
========================= --}}
<div class="page">
    <table class="hdr">
        <tr>
            <td class="logo-wrap">
                @if($logoData)
                    <img class="logo" src="{{ $logoData }}" alt="Logo">
                @endif
            </td>

            <td class="center-wrap">
                <div class="clinic-name">KRYS&amp;TELL DENTAL CENTER</div>
                <div class="clinic-line">CT Building Jose Romeo Road, Bagacay Dumaguete City</div>
                <div class="clinic-line">09772443595</div>
                <div class="clinic-line">Krys&amp;Tell Dental Center</div>
            </td>

            <td class="chart-wrap">
                <div class="chart-title">DENTAL CHART</div>
                <div class="chart-box"></div>
            </td>
        </tr>
    </table>

    <div class="section-title">INFORMED CONSENT (SUMMARY)</div>
    <div class="rule"></div>

    @php
        $answers = $consent?->initials ?? []; // still named initials in your DB; now can hold Yes/No too
        if (!is_array($answers)) $answers = (array) $answers;
    @endphp

    <table class="consent-table">
        <thead>
            <tr>
                <th style="width:60%;">Section</th>
                <th style="width:20%;">Yes</th>
                <th style="width:20%;">No</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consentSections as $key => $label)
                @php
                    $v = $answers[$key] ?? '';
                    $yn = $yesNo($v); // 'Yes' / 'No' / ''
                @endphp
                <tr>
                    <td><strong>{{ $label }}</strong></td>
                    <td class="yn">[{{ $yn === 'Yes' ? 'x' : ' ' }}]</td>
                    <td class="yn">[{{ $yn === 'No' ? 'x' : ' ' }}]</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sig2">
        <tr>
            <td>
                <div class="sig-cap">Patient/Guardian Signature</div>
                @if($sigConsentPatientData)
                    <img class="sig-prev" src="{{ $sigConsentPatientData }}" alt="Consent Patient Signature">
                @else
                    <div style="height:120px; border:1px solid #000;"></div>
                @endif
            </td>
            <td>
                <div class="sig-cap">Dentist Signature</div>
                @if($sigConsentDentistData)
                    <img class="sig-prev" src="{{ $sigConsentDentistData }}" alt="Consent Dentist Signature">
                @else
                    <div style="height:120px; border:1px solid #000;"></div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
