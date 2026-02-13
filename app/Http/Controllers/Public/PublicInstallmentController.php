<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicInstallmentController extends Controller
{
    /**
     * Resolve the current user's patient IDs.
     *
     * We try (in order): patients.user_id, patients.email, patients.contact_number.
     */
    private function patientIdsForCurrentUser(): array
    {
        $user = auth()->user();
        if (!$user || !Schema::hasTable('patients')) return [];

        $pq = Patient::query();

        // 1) Direct FK (if your schema has it)
        if (Schema::hasColumn('patients', 'user_id')) {
            $pq->where('user_id', $user->id);
            return $pq->pluck('id')->all();
        }

        // 2) Match by email
        $email = strtolower(trim((string)($user->email ?? '')));
        if ($email !== '' && Schema::hasColumn('patients', 'email')) {
            $pq->whereRaw('LOWER(email) = ?', [$email]);
            $ids = $pq->pluck('id')->all();
            if (!empty($ids)) return $ids;
        }

        // 3) Match by phone
        if (
            Schema::hasColumn('patients', 'contact_number') &&
            Schema::hasColumn('users', 'phone_number') &&
            !empty($user->phone_number)
        ) {
            $pq = Patient::query()->where('contact_number', $user->phone_number);
            $ids = $pq->pluck('id')->all();
            if (!empty($ids)) return $ids;
        }

        return [];
    }

    public function index(Request $request)
    {
        $patientIds = $this->patientIdsForCurrentUser();

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

        return view('public.installments.index', compact('plans'));
    }

    public function show(InstallmentPlan $plan)
{
    $patientIds = $this->patientIdsForCurrentUser();

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