<?php

namespace App\Services;

use App\Models\InstallmentPlan;
use App\Models\Visit;
use Illuminate\Support\Collection;

class RecementContextService
{
    public function options(int $patientId): Collection
    {
        $bracesService = fn ($query) => $query->where(fn ($name) => $name
            ->whereLike('name', '%brace%')->orWhereLike('name', '%orthodont%'));

        $plans = InstallmentPlan::with('service')
            ->where('patient_id', $patientId)
            ->whereHas('service', $bracesService)
            ->orderByDesc('start_date')->orderByDesc('id')->get()
            ->map(fn ($plan) => [
                'value' => 'plan:'.$plan->id,
                'label' => 'Braces plan #'.$plan->id.' — '.($plan->service?->name ?: 'Braces')
                    .' ('.$plan->start_date?->format('M j, Y').')',
            ]);

        $visits = Visit::with('installmentPlan')
            ->where('patient_id', $patientId)
            ->whereDoesntHave('installmentPlan')
            ->whereHas('procedures.service', $bracesService)
            ->orderByDesc('visit_date')->orderByDesc('id')->get()
            ->map(fn ($visit) => [
                'value' => 'visit:'.$visit->id,
                'label' => 'Braces visit #'.$visit->id.' ('.$visit->visit_date?->format('M j, Y').')',
            ]);

        return $plans->concat($visits)->values();
    }

    public function attributes(?string $value, int $patientId): ?array
    {
        if ($value === null || $value === '') return ['related_visit_id' => null, 'related_installment_plan_id' => null];
        if (!$this->options($patientId)->contains(fn ($option) => $option['value'] === $value)) return null;
        [$kind, $id] = explode(':', $value, 2);

        return [
            'related_visit_id' => $kind === 'visit' ? (int) $id : null,
            'related_installment_plan_id' => $kind === 'plan' ? (int) $id : null,
        ];
    }
}
