<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Information Record</title>
    <style>
        @page { size: letter; margin: 0.5in; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color:#000; }
        .top { display:flex; justify-content:space-between; align-items:flex-start; }
        .brand { display:flex; gap:12px; align-items:center; }
        .logoBox {
            width: 90px; height: 70px;
            border: 1px solid #ddd;
            display:flex; align-items:center; justify-content:center;
            font-size:10px; color:#777;
        }
        .clinic h1 { margin:0; font-size:14px; font-weight:800; }
        .clinic .sub { font-size:10px; line-height:1.2; margin-top:2px; }
        .rightBox { text-align:right; }
        .chart {
            display:inline-block;
            border: 2px solid #000;
            padding: 6px 10px;
            font-weight:800;
            font-size:11px;
            border-radius: 6px;
        }
        .chartBox {
            margin-top:6px;
            width: 140px; height: 55px;
            border: 1px solid #000;
            border-radius: 10px;
        }

        .title { margin-top: 10px; font-size:12px; font-weight:900; }
        .hr { border-top: 2px solid #000; margin: 6px 0 10px; }

        .row { display:flex; gap:10px; }
        .col { flex:1; }
        .field { margin-bottom: 4px; }
        .label { display:inline-block; min-width: 95px; font-weight:700; }
        .line {
            display:inline-block;
            border-bottom: 1px solid #000;
            min-width: 220px;
            padding: 0 4px 1px;
        }
        .line.sm { min-width: 120px; }
        .line.xs { min-width: 80px; }
        .line.lg { min-width: 320px; }

        .req { color:#c00000; font-weight:900; } /* red check mark */
        .section { margin-top: 6px; }
        .section h2{
            margin: 8px 0 4px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .q { margin: 2px 0; }
        .box { display:inline-block; width: 10px; height: 10px; border: 1px solid #000; margin-right:6px; vertical-align:middle; }
        .checked { background:#000; }

        .grid3 { display:flex; gap:12px; margin-top:4px; }
        .grid3 .col { flex:1; }

        .sigRow { margin-top: 10px; display:flex; justify-content:flex-end; }
        .sigBox {
            width: 240px;
            text-align:center;
        }
        .sigLine { border-top: 1px solid #000; margin-top: 6px; }
        .sigImg { width: 240px; height: 70px; object-fit: contain; }

        .small { font-size:10px; }
    </style>
</head>
<body>
@php
    $info = $info ?? null;

    $fullName = trim(($patient->last_name ?? '').', '.($patient->first_name ?? '').' '.($patient->middle_name ?? ''));
    $birth = $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('m/d/Y') : '';
    $sex = $patient->gender ? (strtolower($patient->gender) === 'male' ? 'M' : (strtolower($patient->gender) === 'female' ? 'F' : $patient->gender)) : '';
    $ageVal = $age !== null ? $age : '';
@endphp

<div class="top">
    <div class="brand">
        <div class="logoBox">LOGO</div>
        <div class="clinic">
            <h1>KRYS&TELL DENTAL CENTER</h1>
            <div class="sub">
                CT Building Jose Romeo Road, Bagacay Dumaguete City<br>
                09772443595<br>
                Krys&Tell Dental Center
            </div>
        </div>
    </div>

    <div class="rightBox">
        <div class="chart">DENTAL CHART</div>
        <div class="chartBox"></div>
    </div>
</div>

<div class="title">PATIENT INFORMATION RECORD</div>
<div class="hr"></div>

<div class="row">
    <div class="col">
        <div class="field">
            <span class="label">Name:</span>
            <span class="req">✓</span>
            <span class="line lg">{{ $fullName }}</span>
        </div>

        <div class="field">
            <span class="label">Home Address:</span>
            <span class="req">✓</span>
            <span class="line lg">{{ $patient->address ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Birthdate:</span>
            <span class="req">✓</span>
            <span class="line sm">{{ $birth }}</span>
        </div>

        <div class="field">
            <span class="label">Occupation:</span>
            <span class="req">✓</span>
            <span class="line lg">{{ $info->occupation ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Dental Insurance:</span>
            <span class="line lg">{{ $info->dental_insurance ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Effective Date:</span>
            <span class="line sm">
                {{ $info && $info->effective_date ? \Carbon\Carbon::parse($info->effective_date)->format('m/d/Y') : '' }}
            </span>
        </div>

        <div class="section">
            <div class="small"><strong>For Minors:</strong></div>
            <div class="field">
                <span class="label">Parent/Guardian’s Name:</span>
                <span class="{{ ($info && $info->is_minor) ? 'req' : '' }}">{{ ($info && $info->is_minor) ? '✓' : '' }}</span>
                <span class="line lg">{{ $info->guardian_name ?? '' }}</span>
            </div>
            <div class="field">
                <span class="label">Occupation:</span>
                <span class="{{ ($info && $info->is_minor) ? 'req' : '' }}">{{ ($info && $info->is_minor) ? '✓' : '' }}</span>
                <span class="line lg">{{ $info->guardian_occupation ?? '' }}</span>
            </div>
        </div>

        <div class="field">
            <span class="label">Whom may we thank for referring you?</span>
            <span class="line lg">{{ $info->referral_source ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Reason for dental consultation?</span>
            <span class="line lg">{{ $info->consultation_reason ?? '' }}</span>
        </div>
    </div>

    <div class="col">
        <div class="field">
            <span class="label">Sex:</span>
            <span class="req">✓</span>
            <span class="line xs">{{ $sex }}</span>
        </div>

        <div class="field">
            <span class="label">Age:</span>
            <span class="line xs">{{ $ageVal }}</span>
        </div>

        <div class="field">
            <span class="label">Nickname:</span>
            <span class="req">✓</span>
            <span class="line sm">{{ $info->nickname ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Home No.:</span>
            <span class="line sm">{{ $info->home_no ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Office No.:</span>
            <span class="line sm">{{ $info->office_no ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Fax No.:</span>
            <span class="line sm">{{ $info->fax_no ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Cell/Mobile No.:</span>
            <span class="req">✓</span>
            <span class="line sm">{{ $patient->contact_number ?? '' }}</span>
        </div>

        <div class="field">
            <span class="label">Email Add:</span>
            <span class="req">✓</span>
            <span class="line sm">{{ $patient->email ?? '' }}</span>
        </div>
    </div>
</div>

<div class="section">
    <h2>DENTAL HISTORY</h2>
    <div class="field">
        <span class="label">Previous Dentist/Dr.:</span>
        <span class="line lg">{{ $info->previous_dentist ?? '' }}</span>
        <span class="label" style="min-width:120px;">Last Dental Visit:</span>
        <span class="line sm">
            {{ $info && $info->last_dental_visit ? \Carbon\Carbon::parse($info->last_dental_visit)->format('m/d/Y') : '' }}
        </span>
    </div>
</div>

<div class="section">
    <h2>MEDICAL HISTORY</h2>
    <div class="field">
        <span class="label">Name of Physician:</span>
        <span class="line lg">{{ $info->physician_name ?? '' }}</span>
        <span class="label" style="min-width:120px;">Specialty:</span>
        <span class="line sm">{{ $info->physician_specialty ?? '' }}</span>
    </div>

    <div class="q">1. Are you in good health? <span class="line sm">{{ is_null($info?->good_health) ? '' : ($info->good_health ? 'Yes' : 'No') }}</span></div>
    <div class="q">2. Are you under medical treatment now? <span class="line sm">{{ is_null($info?->under_treatment) ? '' : ($info->under_treatment ? 'Yes' : 'No') }}</span></div>
    <div class="q small">a. If yes, what condition being treated? <span class="line lg">{{ $info->treatment_condition ?? '' }}</span></div>

    <div class="q">3. Serious illness or surgical operation? <span class="line sm">{{ is_null($info?->serious_illness) ? '' : ($info->serious_illness ? 'Yes' : 'No') }}</span></div>
    <div class="q small">a. If yes, what illness/operation? <span class="line lg">{{ $info->serious_illness_details ?? '' }}</span></div>

    <div class="q">4. Have you ever been hospitalized? <span class="line sm">{{ is_null($info?->hospitalized) ? '' : ($info->hospitalized ? 'Yes' : 'No') }}</span></div>
    <div class="q small">a. If yes, please specify <span class="line lg">{{ $info->hospitalized_reason ?? '' }}</span></div>

    <div class="q">5. Taking prescription/nonprescription medication? <span class="line sm">{{ is_null($info?->taking_medication) ? '' : ($info->taking_medication ? 'Yes' : 'No') }}</span></div>
    <div class="q small">a. If yes, please specify <span class="line lg">{{ $info->medications ?? '' }}</span></div>

    <div class="q">6. Do you take aspirin regularly? <span class="line sm">{{ is_null($info?->takes_aspirin) ? '' : ($info->takes_aspirin ? 'Yes' : 'No') }}</span></div>

    <div class="q">7. Do you use tobacco products? <span class="line sm">{{ is_null($info?->tobacco_use) ? '' : ($info->tobacco_use ? 'Yes' : 'No') }}</span></div>
    <div class="q">8. Do you use alcohol, cocaine or other dangerous drugs? <span class="line sm">{{ is_null($info?->dangerous_drugs) ? '' : ($info->dangerous_drugs ? 'Yes' : 'No') }}</span></div>

    <div class="q">9. Bleeding Time: <span class="line sm">{{ $info->bleeding_time ?? '' }}</span></div>

    <div class="q">10. For women only: Pregnant? <span class="line xs">{{ is_null($info?->pregnant) ? '' : ($info->pregnant ? 'Yes' : 'No') }}</span>
        &nbsp; Nursing? <span class="line xs">{{ is_null($info?->nursing) ? '' : ($info->nursing ? 'Yes' : 'No') }}</span>
        &nbsp; Birth control pills? <span class="line xs">{{ is_null($info?->birth_control_pills) ? '' : ($info->birth_control_pills ? 'Yes' : 'No') }}</span>
    </div>

    <div class="q">11. Blood Type: <span class="line xs">{{ $info->blood_type ?? '' }}</span></div>
    <div class="q">12. Blood Pressure: <span class="line xs">{{ $info->blood_pressure ?? '' }}</span></div>

    <div class="q" style="margin-top:6px;">
        13. Do you have or have you had any of the following? Check which apply.
    </div>

    @php
        $conds = $info->medical_conditions ?? [];
        $isChecked = function($name) use ($conds){
            return in_array($name, $conds) ? true : false;
        };
        $mk = function($checked){
            // Dompdf supports simple blocks; use filled box for checked
            return $checked ? '■' : '□';
        };

        // Three columns like your form
        $col1 = [
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
            'Arthritis / Rheumatism',
            'Other',
        ];
    @endphp

    <div class="grid3">
        <div class="col">
            @foreach($col1 as $c)
                <div class="q small">{{ $mk($isChecked($c)) }} {{ $c }}</div>
            @endforeach
        </div>
        <div class="col">
            @foreach($col2 as $c)
                <div class="q small">{{ $mk($isChecked($c)) }} {{ $c }}</div>
            @endforeach
        </div>
        <div class="col">
            @foreach($col3 as $c)
                <div class="q small">{{ $mk($isChecked($c)) }} {{ $c }}</div>
            @endforeach
            <div class="q small">Other specify: <span class="line sm">{{ $info->medical_conditions_other ?? '' }}</span></div>
        </div>
    </div>
</div>

<div class="sigRow">
    <div class="sigBox">
        @if($signatureBase64)
            <img class="sigImg" src="{{ $signatureBase64 }}" alt="Signature">
        @else
            <div style="height:70px;"></div>
        @endif
        <div class="sigLine"></div>
        <div style="margin-top:3px; font-weight:700;">Signature</div>
    </div>
</div>

</body>
</html>
