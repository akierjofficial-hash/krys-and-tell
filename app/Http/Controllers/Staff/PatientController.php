<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\PatientInformationRecord;
use App\Models\PatientInformedConsent;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Services\FinancialService;
use App\Services\PatientFileStorage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $qInput = $request->query('q', '');
        $sortInput = $request->query('sort', 'last_asc');
        $initialInput = $request->query('initial', '');
        $perPageInput = $request->query('per_page', 25);
        $q = is_string($qInput) ? trim(mb_substr($qInput, 0, 200)) : '';
        $sort = is_string($sortInput) ? $sortInput : 'last_asc';
        $initial = is_string($initialInput) ? strtoupper(trim($initialInput)) : '';
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 25;

        $allowedSorts = ['last_asc', 'last_desc', 'newest', 'oldest', 'recent_visit'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'last_asc';
        if (!in_array($perPage, [25, 50, 100], true)) $perPage = 25;
        if (!preg_match('/^[A-Z]$/', $initial)) $initial = '';

        $applySearch = function ($query) use ($q): void {
            if ($q === '') return;

            $terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $birthdate = null;
            foreach (['Y-m-d', 'm/d/Y', 'm-d-Y'] as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $q);
                    if ($date && $date->format($format) === $q) {
                        $birthdate = $date->toDateString();
                        break;
                    }
                } catch (\Throwable) {
                    // Search text is not a date in this format.
                }
            }

            $query->where(function ($search) use ($terms, $birthdate) {
                $search->where(function ($names) use ($terms) {
                    foreach ($terms as $term) {
                        $like = '%' . $term . '%';
                        $names->where(function ($part) use ($like) {
                            $part->where('first_name', 'like', $like)
                                ->orWhere('middle_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like)
                                ->orWhere('contact_number', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        });
                    }
                });
                if ($birthdate) $search->orWhereDate('birthdate', $birthdate);
            });
        };

        $letterQuery = Patient::query();
        $applySearch($letterQuery);
        $availableInitials = $letterQuery
            ->selectRaw('UPPER(SUBSTR(TRIM(last_name), 1, 1)) AS initial_letter')
            ->whereNotNull('last_name')
            ->whereRaw("TRIM(last_name) <> ''")
            ->distinct()
            ->pluck('initial_letter')
            ->filter(fn ($letter) => is_string($letter) && preg_match('/^[A-Z]$/', $letter))
            ->values()
            ->all();

        $query = Patient::query()->withMax('visits', 'visit_date');
        $applySearch($query);
        if ($initial !== '') {
            $query->whereRaw('UPPER(SUBSTR(TRIM(last_name), 1, 1)) = ?', [$initial]);
        }

        match ($sort) {
            'last_desc' => $query->orderByRaw('LOWER(TRIM(last_name)) DESC')->orderByRaw('LOWER(TRIM(first_name)) DESC')->orderByDesc('id'),
            'newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'recent_visit' => $query->orderByDesc('visits_max_visit_date')->orderByRaw('LOWER(TRIM(last_name)) ASC')->orderBy('id'),
            default => $query->orderByRaw('LOWER(TRIM(last_name)) ASC')->orderByRaw('LOWER(TRIM(first_name)) ASC')->orderBy('id'),
        };

        $patients = $query->paginate($perPage)->withQueryString();
        if ($patients->total() > 0 && $patients->currentPage() > $patients->lastPage()) {
            return redirect()->route('staff.patients.index', array_merge(
                $request->except('page'),
                ['page' => $patients->lastPage()]
            ));
        }

        return view('staff.patients.index', compact(
            'patients', 'q', 'sort', 'initial', 'perPage', 'availableInitials'
        ));
    }

    public function create()
    {
        return view('staff.patients.create');
    }

    /**
     * Convert request input (0/1) to nullable boolean.
     * - If the key is missing => null
     * - If present and "1" => true
     * - If present and "0" => false
     */
    private function nullableBool(Request $request, string $key): ?bool
    {
        if (!$request->has($key)) return null;

        $v = $request->input($key);

        if ($v === null || $v === '') return null;
        if ($v === 1 || $v === '1' || $v === true || $v === 'true') return true;
        if ($v === 0 || $v === '0' || $v === false || $v === 'false') return false;

        return null;
    }

    private function storeSignature(?string $dataUrl, string $folder): ?array
    {
        return app(PatientFileStorage::class)->storeSignature($dataUrl, $folder);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Patient core
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'birthdate'      => 'required|date',
            'gender'         => 'required|string|in:Male,Female,Other',
            'contact_number' => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:1000',

            // Patient Information Record (optional for now)
            'nickname'              => 'nullable|string|max:255',
            'occupation'            => 'nullable|string|max:255',
            'dental_insurance'      => 'nullable|string|max:255',
            'effective_date'        => 'nullable|date',

            'home_no'               => 'nullable|string|max:50',
            'office_no'             => 'nullable|string|max:50',
            'fax_no'                => 'nullable|string|max:50',

            'is_minor'              => 'nullable|in:0,1',
            'guardian_name'         => 'nullable|string|max:255',
            'guardian_occupation'   => 'nullable|string|max:255',

            'referral_source'       => 'nullable|string|max:255',
            'consultation_reason'   => 'nullable|string|max:2000',

            'previous_dentist'      => 'nullable|string|max:255',
            'last_dental_visit'     => 'nullable|date',

            'physician_name'        => 'nullable|string|max:255',
            'physician_specialty'   => 'nullable|string|max:255',

            'good_health'           => 'nullable|in:0,1',
            'under_treatment'       => 'nullable|in:0,1',
            'treatment_condition'   => 'nullable|string|max:255',

            'serious_illness'           => 'nullable|in:0,1',
            'serious_illness_details'   => 'nullable|string|max:2000',

            'hospitalized'          => 'nullable|in:0,1',
            'hospitalized_reason'   => 'nullable|string|max:2000',

            'taking_medication'     => 'nullable|in:0,1',
            'medications'           => 'nullable|string|max:2000',
            'takes_aspirin'         => 'nullable|in:0,1',

            'allergies'             => 'nullable|array',
            'allergies.*'           => 'nullable|string|max:60',
            'allergies_other'       => 'nullable|string|max:255',

            'tobacco_use'           => 'nullable|in:0,1',
            'alcohol_use'           => 'nullable|in:0,1',
            'dangerous_drugs'       => 'nullable|in:0,1',

            'bleeding_time'         => 'nullable|string|max:255',

            'pregnant'              => 'nullable|in:0,1',
            'nursing'               => 'nullable|in:0,1',
            'birth_control_pills'   => 'nullable|in:0,1',

            'blood_type'            => 'nullable|string|max:20',
            'blood_pressure'        => 'nullable|string|max:20',

            'medical_conditions'        => 'nullable|array',
            'medical_conditions.*'      => 'nullable|string|max:80',
            'medical_conditions_other'  => 'nullable|string|max:255',

            // Signatures (optional)
            'patient_info_signature'    => 'nullable|string|max:800000',

            // Informed consent (optional)
            'consent_initials'          => 'nullable|array',
            'consent_initials.*'        => 'nullable|string|max:10',
            'consent_patient_signature' => 'nullable|string|max:800000',
            'consent_dentist_signature' => 'nullable|string|max:800000',
        ]);

        $forceCreate = $request->boolean('force_create');

        if (!$forceCreate) {
            $first = mb_strtolower(trim($validated['first_name']));
            $last  = mb_strtolower(trim($validated['last_name']));
            $birth = $validated['birthdate'] ?? null;

            $dupes = Patient::query()
                ->whereRaw('LOWER(first_name) = ? AND LOWER(last_name) = ?', [$first, $last])
                ->when($birth, fn ($q) => $q->whereDate('birthdate', $birth))
                ->orderByDesc('created_at')
                ->take(5)
                ->get(['id', 'first_name', 'middle_name', 'last_name', 'birthdate', 'contact_number']);

            if ($dupes->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->with('duplicate_candidates', $dupes);
            }
        }

        $patientData = collect($validated)->only([
            'first_name','last_name','middle_name','birthdate','gender',
            'contact_number','email','address','notes',
        ])->toArray();

        $infoData = [
            'nickname'            => $validated['nickname'] ?? null,
            'occupation'          => $validated['occupation'] ?? null,
            'dental_insurance'    => $validated['dental_insurance'] ?? null,
            'effective_date'      => $validated['effective_date'] ?? null,

            'home_no'             => $validated['home_no'] ?? null,
            'office_no'           => $validated['office_no'] ?? null,
            'fax_no'              => $validated['fax_no'] ?? null,

            'is_minor'            => $request->boolean('is_minor'),
            'guardian_name'       => $validated['guardian_name'] ?? null,
            'guardian_occupation' => $validated['guardian_occupation'] ?? null,

            'referral_source'     => $validated['referral_source'] ?? null,
            'consultation_reason' => $validated['consultation_reason'] ?? null,

            'previous_dentist'    => $validated['previous_dentist'] ?? null,
            'last_dental_visit'   => $validated['last_dental_visit'] ?? null,

            'physician_name'      => $validated['physician_name'] ?? null,
            'physician_specialty' => $validated['physician_specialty'] ?? null,

            'good_health'         => $this->nullableBool($request, 'good_health'),
            'under_treatment'     => $this->nullableBool($request, 'under_treatment'),
            'treatment_condition' => $validated['treatment_condition'] ?? null,

            'serious_illness'         => $this->nullableBool($request, 'serious_illness'),
            'serious_illness_details' => $validated['serious_illness_details'] ?? null,

            'hospitalized'        => $this->nullableBool($request, 'hospitalized'),
            'hospitalized_reason' => $validated['hospitalized_reason'] ?? null,

            'taking_medication'   => $this->nullableBool($request, 'taking_medication'),
            'medications'         => $validated['medications'] ?? null,
            'takes_aspirin'       => $this->nullableBool($request, 'takes_aspirin'),

            'allergies'           => !empty($validated['allergies']) ? array_values(array_filter($validated['allergies'])) : null,
            'allergies_other'     => $validated['allergies_other'] ?? null,

            'tobacco_use'         => $this->nullableBool($request, 'tobacco_use'),
            'alcohol_use'         => $this->nullableBool($request, 'alcohol_use'),
            'dangerous_drugs'     => $this->nullableBool($request, 'dangerous_drugs'),

            'bleeding_time'       => $validated['bleeding_time'] ?? null,

            'pregnant'            => $this->nullableBool($request, 'pregnant'),
            'nursing'             => $this->nullableBool($request, 'nursing'),
            'birth_control_pills' => $this->nullableBool($request, 'birth_control_pills'),

            'blood_type'          => $validated['blood_type'] ?? null,
            'blood_pressure'      => $validated['blood_pressure'] ?? null,

            'medical_conditions'       => !empty($validated['medical_conditions']) ? array_values(array_filter($validated['medical_conditions'])) : null,
            'medical_conditions_other' => $validated['medical_conditions_other'] ?? null,
        ];

        $consentData = [
            'initials' => !empty($validated['consent_initials']) ? $validated['consent_initials'] : null,
        ];

        $patient = null;
        DB::transaction(function () use ($patientData, $infoData, $consentData, $request, &$patient) {
            $patient = Patient::create($patientData);

            $sigInfo = $this->storeSignature($request->input('patient_info_signature'), 'signatures/patient-info');
            if ($sigInfo) {
                $infoData['signature_path'] = $sigInfo['path'];
                $infoData['signature_disk'] = $sigInfo['disk'];
                $infoData['signed_at'] = now();
            }

            PatientInformationRecord::create(array_merge(
                ['patient_id' => $patient->id],
                $infoData
            ));

            $sigConsentPatient = $this->storeSignature($request->input('consent_patient_signature'), 'signatures/consent/patient');
            if ($sigConsentPatient) {
                $consentData['patient_signature_path'] = $sigConsentPatient['path'];
                $consentData['patient_signature_disk'] = $sigConsentPatient['disk'];
                $consentData['patient_signed_at'] = now();
            }

            $sigConsentDentist = $this->storeSignature($request->input('consent_dentist_signature'), 'signatures/consent/dentist');
            if ($sigConsentDentist) {
                $consentData['dentist_signature_path'] = $sigConsentDentist['path'];
                $consentData['dentist_signature_disk'] = $sigConsentDentist['disk'];
                $consentData['dentist_signed_at'] = now();
            }

            PatientInformedConsent::create(array_merge(
                ['patient_id' => $patient->id],
                $consentData
            ));
        });

        return $this->ktRedirectToReturn($request, 'staff.patients.index')
            ->with('success', 'Patient added successfully!');
    }

    public function printInfo(\App\Models\Patient $patient, PatientFileStorage $files)
    {
        $patient->loadMissing(['informationRecord', 'informedConsent']);

        $info = $patient->informationRecord;

        $age = null;
        if ($patient->birthdate) {
            $age = Carbon::parse($patient->birthdate)->age;
        }

        $consent = $patient->informedConsent;
        $signatureBase64 = $files->dataUri($info?->signature_disk, $info?->signature_path);
        $consentPatientSignatureBase64 = $files->dataUri($consent?->patient_signature_disk, $consent?->patient_signature_path);
        $consentDentistSignatureBase64 = $files->dataUri($consent?->dentist_signature_disk, $consent?->dentist_signature_path);

        $pdf = Pdf::loadView('staff.patients.print.patient_information', [
            'patient' => $patient,
            'info' => $info,
            'age' => $age,
            'signatureBase64' => $signatureBase64,
            'consentPatientSignatureBase64' => $consentPatientSignatureBase64,
            'consentDentistSignatureBase64' => $consentDentistSignatureBase64,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("patient-{$patient->id}-patient-information.pdf");
    }

    public function show(Patient $patient, FinancialService $finance)
    {
        $patient->loadMissing(['informationRecord', 'informedConsent', 'files']);

        $visits = $patient->visits()
            ->with(['procedures.service'])
            ->orderByDesc('visit_date')
            ->paginate(10, ['*'], 'visits_page');

        $appointments = $patient->appointments()
            ->with('service')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(10, ['*'], 'appointments_page');

        $payments = $patient->payments()
            ->with(['visit.procedures.service', 'procedure.service'])
            ->orderByDesc('payment_date')
            ->paginate(10, ['*'], 'payments_page');

        $cashTotalPaid = (float) $patient->payments()->sum('amount');
        $cashPaymentsCount = (int) $patient->payments()->count();

        $installmentPlans = InstallmentPlan::where('patient_id', $patient->id)
            ->with(['service', 'visit', 'payments'])
            ->orderByDesc('created_at')
            ->get();
        $installmentPlans->each(fn ($plan) => $plan->computed_balance = $finance->planBalance($plan));

        $installmentPayments = InstallmentPayment::whereHas('plan', function ($q) use ($patient) {
                $q->where('patient_id', $patient->id);
            })
            ->with(['plan.service', 'visit'])
            ->orderByDesc('payment_date')
            ->paginate(10, ['*'], 'installment_payments_page');

        $installmentTotalPaid = (float) $installmentPlans->sum(fn ($plan) => $finance->planPaid($plan));

        $installmentPaymentsCount = (int) InstallmentPayment::whereHas('plan', function ($q) use ($patient) {
            $q->where('patient_id', $patient->id);
        })->count();

        $grandTotalPaid = $cashTotalPaid + $installmentTotalPaid;
        $paymentsAllCount = $cashPaymentsCount + $installmentPaymentsCount;

        $totalPaid = $cashTotalPaid;

        $activeTab = request('tab', 'tab-info');
        $validTabs = ['tab-info','tab-consent','tab-visits','tab-appts','tab-payments'];
        if (!in_array($activeTab, $validTabs, true)) $activeTab = 'tab-info';

        return view('staff.patients.show', compact(
            'patient',
            'visits',
            'appointments',
            'payments',
            'totalPaid',

            'installmentPlans',
            'installmentPayments',

            'cashTotalPaid',
            'cashPaymentsCount',
            'installmentTotalPaid',
            'installmentPaymentsCount',
            'grandTotalPaid',
            'paymentsAllCount',
            'activeTab'
        ));
    }

    public function edit(Patient $patient)
    {
        $patient->loadMissing(['informationRecord', 'informedConsent']);
        return view('staff.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            // Patient core
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'birthdate'      => 'required|date',
            'gender'         => 'required|string|in:Male,Female,Other',
            'contact_number' => 'required|string|max:20',
            'email'          => 'required|email|max:255',
            'address'        => 'required|string|max:500',
            'notes'          => 'nullable|string|max:1000',

            // Info record (✅ nickname now OPTIONAL)
            'nickname'            => 'nullable|string|max:255',
            'occupation'          => 'required|string|max:255',
            'dental_insurance'    => 'nullable|string|max:255',
            'effective_date'      => 'nullable|date',
            'home_no'             => 'nullable|string|max:50',
            'office_no'           => 'nullable|string|max:50',
            'fax_no'              => 'nullable|string|max:50',

            'is_minor'            => 'nullable|in:0,1',
            'guardian_name'       => 'nullable|string|max:255',
            'guardian_occupation' => 'nullable|string|max:255',

            'referral_source'     => 'nullable|string|max:255',
            'consultation_reason' => 'nullable|string|max:2000',

            'previous_dentist'    => 'nullable|string|max:255',
            'last_dental_visit'   => 'nullable|date',

            'physician_name'      => 'nullable|string|max:255',
            'physician_specialty' => 'nullable|string|max:255',

            'good_health'         => 'nullable|in:0,1',
            'under_treatment'     => 'nullable|in:0,1',
            'treatment_condition' => 'nullable|string|max:255',

            'serious_illness'         => 'nullable|in:0,1',
            'serious_illness_details' => 'nullable|string|max:2000',

            'hospitalized'        => 'nullable|in:0,1',
            'hospitalized_reason' => 'nullable|string|max:2000',

            'taking_medication'   => 'nullable|in:0,1',
            'medications'         => 'nullable|string|max:2000',
            'takes_aspirin'       => 'nullable|in:0,1',

            'allergies'           => 'nullable|array',
            'allergies.*'         => 'nullable|string|max:60',
            'allergies_other'     => 'nullable|string|max:255',

            'tobacco_use'         => 'nullable|in:0,1',
            'alcohol_use'         => 'nullable|in:0,1',
            'dangerous_drugs'     => 'nullable|in:0,1',

            'bleeding_time'       => 'nullable|string|max:255',

            'blood_type'          => 'nullable|string|max:20',
            'blood_pressure'      => 'nullable|string|max:20',

            'medical_conditions'       => 'nullable|array',
            'medical_conditions.*'     => 'nullable|string|max:80',
            'medical_conditions_other' => 'nullable|string|max:255',

            // Signatures (optional on edit)
            'patient_info_signature'    => 'nullable|string|max:800000',
            'consent_patient_signature' => 'nullable|string|max:800000',
            'consent_dentist_signature' => 'nullable|string|max:800000',

            // Consent (✅ not required; hidden inputs still provide "No")
            'consent_initials'     => 'nullable|array',
            'consent_initials.*'   => 'nullable|string|max:10',
        ]);

        $patient->update([
            'first_name'     => $validated['first_name'],
            'last_name'      => $validated['last_name'],
            'middle_name'    => $validated['middle_name'] ?? null,
            'birthdate'      => $validated['birthdate'],
            'gender'         => $validated['gender'],
            'contact_number' => $validated['contact_number'],
            'email'          => $validated['email'],
            'address'        => $validated['address'],
            'notes'          => $validated['notes'] ?? null,
        ]);

        $info = $patient->informationRecord()->firstOrNew([]);

        $info->fill([
            'nickname'            => $validated['nickname'] ?? null,
            'occupation'          => $validated['occupation'],
            'dental_insurance'    => $validated['dental_insurance'] ?? null,
            'effective_date'      => $validated['effective_date'] ?? null,
            'home_no'             => $validated['home_no'] ?? null,
            'office_no'           => $validated['office_no'] ?? null,
            'fax_no'              => $validated['fax_no'] ?? null,

            'is_minor'            => $request->boolean('is_minor'),
            'guardian_name'       => $validated['guardian_name'] ?? null,
            'guardian_occupation' => $validated['guardian_occupation'] ?? null,

            'referral_source'     => $validated['referral_source'] ?? null,
            'consultation_reason' => $validated['consultation_reason'] ?? null,

            'previous_dentist'    => $validated['previous_dentist'] ?? null,
            'last_dental_visit'   => $validated['last_dental_visit'] ?? null,

            'physician_name'      => $validated['physician_name'] ?? null,
            'physician_specialty' => $validated['physician_specialty'] ?? null,

            // ✅ Use your helper for nullable booleans (consistent with store)
            'good_health'         => $this->nullableBool($request, 'good_health'),
            'under_treatment'     => $this->nullableBool($request, 'under_treatment'),
            'treatment_condition' => $validated['treatment_condition'] ?? null,

            'serious_illness'         => $this->nullableBool($request, 'serious_illness'),
            'serious_illness_details' => $validated['serious_illness_details'] ?? null,

            'hospitalized'        => $this->nullableBool($request, 'hospitalized'),
            'hospitalized_reason' => $validated['hospitalized_reason'] ?? null,

            'taking_medication'   => $this->nullableBool($request, 'taking_medication'),
            'medications'         => $validated['medications'] ?? null,
            'takes_aspirin'       => $this->nullableBool($request, 'takes_aspirin'),

            'allergies'           => !empty($validated['allergies']) ? array_values(array_filter($validated['allergies'])) : null,
            'allergies_other'     => $validated['allergies_other'] ?? null,

            'tobacco_use'         => $this->nullableBool($request, 'tobacco_use'),
            'alcohol_use'         => $this->nullableBool($request, 'alcohol_use'),
            'dangerous_drugs'     => $this->nullableBool($request, 'dangerous_drugs'),

            'bleeding_time'       => $validated['bleeding_time'] ?? null,

            'blood_type'          => $validated['blood_type'] ?? null,
            'blood_pressure'      => $validated['blood_pressure'] ?? null,

            'medical_conditions'       => !empty($validated['medical_conditions']) ? array_values(array_filter($validated['medical_conditions'])) : null,
            'medical_conditions_other' => $validated['medical_conditions_other'] ?? null,
        ]);

        if ($request->filled('patient_info_signature')) {
            $signature = $this->storeSignature($request->input('patient_info_signature'), 'signatures/patient-info');
            if ($signature) {
                $info->signature_path = $signature['path'];
                $info->signature_disk = $signature['disk'];
                $info->signed_at = now();
            }
        }

        $info->patient_id = $patient->id;
        $info->save();

        $consent = $patient->informedConsent()->firstOrNew([]);
        $consent->patient_id = $patient->id;

        // ✅ keep initials nullable (if none submitted, store null)
        $consent->initials = $validated['consent_initials'] ?? null;

        if ($request->filled('consent_patient_signature')) {
            $signature = $this->storeSignature($request->input('consent_patient_signature'), 'signatures/consent/patient');
            if ($signature) {
                $consent->patient_signature_path = $signature['path'];
                $consent->patient_signature_disk = $signature['disk'];
                $consent->patient_signed_at = now();
            }
        }

        if ($request->filled('consent_dentist_signature')) {
            $signature = $this->storeSignature($request->input('consent_dentist_signature'), 'signatures/consent/dentist');
            if ($signature) {
                $consent->dentist_signature_path = $signature['path'];
                $consent->dentist_signature_disk = $signature['disk'];
                $consent->dentist_signed_at = now();
            }
        }

        $consent->save();

        return $this->ktRedirectToReturn($request, 'staff.patients.index')
            ->with('success', 'Patient record updated successfully!');
    }

    public function restore(Request $request, int $id)
    {
        $patient = Patient::withTrashed()->findOrFail($id);
        $patient->restore();

        return $this->ktRedirectToReturn($request, 'staff.patients.index')
            ->with('success', 'Patient restored successfully!');
    }

    public function destroy(Request $request, Patient $patient)
    {
        $name = trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: ('#'.$patient->id);

        $patient->delete();

        $returnUrl = $this->ktReturnUrl($request, 'staff.patients.index');

        return $this->ktRedirectToReturn($request, 'staff.patients.index')
            ->with('success', 'Patient deleted successfully!')
            ->with('undo', [
                'message' => 'Patient deleted: ' . $name,
                'url' => route('staff.patients.restore', ['id' => $patient->id, 'return' => $returnUrl]),
                'ms' => 10000,
            ]);
    }
}
