<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPlan;
use App\Services\PatientAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicInstallmentController extends Controller
{
    private function patientIdsForCurrentUser(PatientAccessService $access): array
    {
        return $access->linkedPatientIds(auth()->user());
    }

    public function index(Request $request, PatientAccessService $access)
    {
        $patientIds = $this->patientIdsForCurrentUser($access);
        $hasVerifiedPatientLink = !empty($patientIds);

        $plans = collect();
        if (!empty($patientIds)) {
            $plans = InstallmentPlan::query()
                ->with([
                    'service',
                    'patient',
                    'visit.patient',
                    'visit.doctor',
                    'payments.visit.doctor',
                    'payments' => fn ($q) => $q->orderBy('month_number')->orderBy('payment_date'),
                ])
                ->where(function ($q) use ($patientIds) {
                    if (Schema::hasColumn('installment_plans', 'patient_id')) {
                        $q->whereIn('patient_id', $patientIds);
                    }

                    $q->orWhereHas('visit', function ($v) use ($patientIds) {
                        if (Schema::hasColumn('visits', 'patient_id')) {
                            $v->whereIn('patient_id', $patientIds);
                        }
                    });
                })
                ->orderByDesc('id')
                ->get();
        }

        return view('public.installments.index', compact('plans', 'hasVerifiedPatientLink'));
    }

    public function show(InstallmentPlan $plan, PatientAccessService $access)
{
    $patientIds = $this->patientIdsForCurrentUser($access);

    // ✅ Enforce ownership (must belong to this user)
    $ownerPatientId = $plan->patient_id
        ?? $plan->visit?->patient_id
        ?? $plan->patient?->id
        ?? $plan->visit?->patient?->id;

    if (empty($patientIds) || !$ownerPatientId || !in_array((int)$ownerPatientId, array_map('intval', $patientIds), true)) {
        abort(403);
    }

    // ✅ Load dentist/doctor info for plan + every payment's visit
    $plan->load([
        'service',
        'patient',
        'visit.patient',
        'visit.doctor',
        'payments' => fn ($q) => $q->orderBy('month_number')->orderBy('payment_date'),
        'payments.visit',
        'payments.visit.doctor',
    ]);

    return view('public.installments.show', compact('plan'));
}

}
