<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecordEntryService
{
    public const METHODS = ['Cash', 'GCash', 'Card', 'Bank Transfer'];

    public static function cents($amount): int
    {
        return (int) round((float) $amount * 100);
    }

    public function validate(array $payload, int $patientId, string $mode): array
    {
        Patient::findOrFail($patientId);
        Validator::make($payload, [
            'visits' => ['required', 'array', 'list', 'min:1', 'max:'.($mode === 'visit' ? 1 : 100)],
            'visits.*' => ['required', 'array'],
            'visits.*.procedures' => ['required', 'array', 'list', 'min:1', 'max:100'],
            'visits.*.procedures.*' => ['required', 'array'],
            'visits.*.payments' => ['sometimes', 'array', 'max:200'],
            'visits.*.payments.*' => ['required', 'array'],
            'visits.*.plan' => ['nullable', 'array'],
            'visits.*.plan.payments' => ['sometimes', 'array', 'max:200'],
            'visits.*.plan.payments.*' => ['required', 'array'],
        ])->validate();

        $money = ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'];
        $rules = ['visits' => ['required', 'array'], 'default_doctor_id' => ['nullable', 'integer', 'exists:doctors,id']];
        foreach ($payload['visits'] as $i => &$visit) {
            $base = "visits.$i";
            $rules["$base.visit_date"] = ['required', 'date_format:Y-m-d', 'before_or_equal:today'];
            $rules["$base.doctor_id"] = ['required', 'integer', 'exists:doctors,id'];
            $rules["$base.notes"] = ['nullable', 'string', 'max:2000'];
            $rules["$base.arrangement"] = ['required', Rule::in(['ordinary', 'installment'])];
            $rules["$base.procedures"] = ['required', 'array'];
            foreach ($visit['procedures'] as $j => $procedure) {
                $p = "$base.procedures.$j";
                $rules["$p.service_id"] = ['required', 'integer', Rule::exists('services', 'id')->whereNull('deleted_at')];
                foreach (['tooth_number' => 50, 'surface' => 10, 'shade' => 10, 'notes' => 2000] as $field => $max) {
                    $rules["$p.$field"] = ['nullable', 'string', "max:$max"];
                }
                $rules["$p.price"] = $money;
            }
            // Blank rows are not receipts. A typed zero is rejected below.
            $visit['payments'] = array_values(array_filter($visit['payments'] ?? [], fn ($p) => isset($p['amount']) && $p['amount'] !== ''));
            $rules["$base.payments"] = ['array'];
            $this->paymentRules($rules, "$base.payments", $visit['payments'], null, count($visit['procedures']));
            if (($visit['arrangement'] ?? '') !== 'installment') {
                unset($visit['plan']);

                continue;
            }
            $rules["$base.plan"] = ['required', 'array'];
            $plan = &$visit['plan'];
            if (! is_array($plan)) {
                $plan = [];
            }
            $rules["$base.plan.total_cost"] = $money;
            $rules["$base.plan.procedure_index"] = ['required', 'integer', 'min:0', 'max:'.(count($visit['procedures']) - 1)];
            $rules["$base.plan.downpayment"] = $money;
            $rules["$base.plan.start_date"] = ['required', 'date_format:Y-m-d', 'before_or_equal:today'];
            $rules["$base.plan.is_open_contract"] = ['required', 'boolean'];
            $rules["$base.plan.months"] = ! empty($plan['is_open_contract']) ? ['nullable', 'integer', 'min:0', 'max:1200'] : ['required', 'integer', 'min:1', 'max:1200'];
            $rules["$base.plan.open_monthly_payment"] = ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'];
            $downpaymentRequired = self::cents($plan['downpayment'] ?? 0) > 0 ? 'required' : 'nullable';
            $rules["$base.plan.downpayment_method"] = [$downpaymentRequired, Rule::in(self::METHODS)];
            $rules["$base.plan.downpayment_date"] = [$downpaymentRequired, 'date_format:Y-m-d', 'before_or_equal:today'];
            $plan['payments'] = array_values(array_filter($plan['payments'] ?? [], fn ($p) => isset($p['amount']) && $p['amount'] !== ''));
            $rules["$base.plan.payments"] = ['array'];
            $this->paymentRules($rules, "$base.plan.payments", $plan['payments'], $patientId);
            unset($plan);
        }
        unset($visit);
        $data = Validator::make($payload, $rules)->validate();
        $errors = [];
        foreach ($data['visits'] as $i => $visit) {
            $charge = array_sum(array_map(fn ($p) => self::cents($p['price']), $visit['procedures']));
            $paid = array_sum(array_map(fn ($p) => self::cents($p['amount']), $visit['payments']));
            if ($charge > 9999999999) {
                $errors["visits.$i.procedures"] = 'Total visit charge is too large.';
            }
            if ($paid > $charge) {
                $errors["visits.$i.payments"] = 'Amounts received cannot exceed the treatment charge.';
            }
            if ($visit['arrangement'] !== 'installment') {
                continue;
            }
            $plan = $visit['plan'];
            $financedProcedure = (int) $plan['procedure_index'];
            $downpayment = self::cents($plan['downpayment']);
            $installmentReceipts = array_sum(array_map(fn ($p) => self::cents($p['amount']), $plan['payments']));
            $totalPaid = $downpayment + $installmentReceipts;
            $planCost = self::cents($plan['total_cost']);
            if ($totalPaid > $planCost) {
                $format = fn (int $cents) => '₱'.number_format($cents / 100, 2);
                $errors["visits.$i.plan.payments"] = 'Installment plan cost '.$format($planCost).'; downpayment '.$format($downpayment)
                    .' plus installment receipts '.$format($installmentReceipts).' is '.$format($totalPaid)
                    .' ('.$format($totalPaid - $planCost).' over). If a receipt paid another treatment, enter it as an ordinary receipt instead. Otherwise correct the plan cost or receipt amount.';
            }
            $ordinaryByProcedure = [];
            foreach ($visit['payments'] as $j => $payment) {
                $key = "visits.$i.payments.$j.procedure_index";
                if (! isset($payment['procedure_index']) || $payment['procedure_index'] === '') {
                    $errors[$key] = 'Choose which non-installment treatment this receipt pays for.';

                    continue;
                }
                $procedureIndex = (int) $payment['procedure_index'];
                if ($procedureIndex === $financedProcedure) {
                    $errors[$key] = 'Use the installment downpayment or installment receipts for the financed treatment.';

                    continue;
                }
                $ordinaryByProcedure[$procedureIndex] = ($ordinaryByProcedure[$procedureIndex] ?? 0) + self::cents($payment['amount']);
            }
            foreach ($ordinaryByProcedure as $procedureIndex => $procedurePaid) {
                if ($procedurePaid > self::cents($visit['procedures'][$procedureIndex]['price'])) {
                    $errors["visits.$i.payments"] = 'Ordinary receipts cannot exceed the charge of the treatment they apply to.';
                }
            }
            $seen = [];
            foreach ($plan['payments'] as $j => $p) {
                $key = "visits.$i.plan.payments.$j";
                $number = (int) $p['month_number'];
                if (isset($seen[$number])) {
                    $errors["$key.month_number"] = 'Use a distinct payment number. Month 0 is reserved for the downpayment.';
                }
                $seen[$number] = true;
                if (! $plan['is_open_contract'] && $number > $plan['months']) {
                    $errors["$key.month_number"] = 'Payment number exceeds the fixed term.';
                }
                if (isset($p['visit_index']) && ! array_key_exists($p['visit_index'], $data['visits'])) {
                    $errors["$key.visit_index"] = 'The related treatment visit was removed.';
                }
                if (isset($p['visit_id'], $p['visit_index'])) {
                    $errors["$key.visit_id"] = 'Choose only one related treatment visit.';
                }
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    private function paymentRules(array &$rules, string $path, array $payments, ?int $patientId = null, ?int $procedureCount = null): void
    {
        foreach ($payments as $i => $payment) {
            $p = "$path.$i";
            $rules["$p.amount"] = ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'];
            $rules["$p.payment_date"] = ['required', 'date_format:Y-m-d', 'before_or_equal:today'];
            $rules["$p.method"] = ['required', Rule::in(self::METHODS)];
            $rules["$p.notes"] = ['nullable', 'string', 'max:2000'];
            if ($procedureCount !== null) {
                $rules["$p.procedure_index"] = ['nullable', 'integer', 'min:0', 'max:'.($procedureCount - 1)];
            }
            if ($patientId !== null) {
                $rules["$p.month_number"] = ['required', 'integer', 'min:1', 'max:100000'];
                $rules["$p.visit_id"] = ['nullable', 'integer', Rule::exists('visits', 'id')->where('patient_id', $patientId)->whereNull('deleted_at')];
                $rules["$p.visit_index"] = ['nullable', 'integer', 'min:0', 'max:99'];
            }
        }
    }

    public function warnings(array $data, int $patientId): array
    {
        $warnings = [];
        $seen = [];
        foreach ($data['visits'] as $i => $visit) {
            foreach ($visit['procedures'] as $j => $p) {
                $signature = implode('|', [$visit['visit_date'], $visit['doctor_id'], $p['service_id'], self::cents($p['price'])]);
                $row = 'Visit '.($i + 1).', procedure '.($j + 1);
                if (isset($seen[$signature])) {
                    $warnings[] = "$row resembles {$seen[$signature]} in this batch (same patient, date, dentist, service and charge).";
                }
                $seen[$signature] = $row;
                $ids = Visit::withTrashed()->where('patient_id', $patientId)
                    ->whereDate('visit_date', $visit['visit_date'])->where('doctor_id', $visit['doctor_id'])
                    ->whereHas('procedures', fn ($q) => $q->where('service_id', $p['service_id'])->where('price', $p['price']))
                    ->orderBy('id')->pluck('id')->implode(', #');
                if ($ids !== '') {
                    $warnings[] = "$row resembles existing visit #$ids (including archived records).";
                }
            }
        }

        return array_values(array_unique($warnings));
    }

    /** Caller owns the transaction and locks the batch/patient before writing. */
    public function create(array $data, int $patientId): array
    {
        $summary = ['visits' => 0, 'procedures' => 0, 'ordinary_payments' => 0, 'installment_plans' => 0, 'installment_payments' => 0];
        $visits = [];
        $procedures = [];
        foreach ($data['visits'] as $i => $row) {
            $paid = array_sum(array_map(fn ($p) => self::cents($p['amount']), $row['payments']));
            $charge = array_sum(array_map(fn ($p) => self::cents($p['price']), $row['procedures']));
            $visit = Visit::create([
                'patient_id' => $patientId, 'doctor_id' => $row['doctor_id'],
                'dentist_name' => Doctor::findOrFail($row['doctor_id'])->name,
                'visit_date' => $row['visit_date'], 'notes' => $row['notes'] ?? null,
                'status' => $row['arrangement'] === 'installment' ? 'installment' : ($paid >= $charge ? 'completed' : ($paid > 0 ? 'partial' : 'pending')),
            ]);
            $visits[$i] = $visit;
            $summary['visits']++;
            foreach ($row['procedures'] as $j => $p) {
                $procedures[$i][$j] = $visit->procedures()->create(collect($p)->only(['service_id', 'tooth_number', 'surface', 'shade', 'price', 'notes'])->all());
                $summary['procedures']++;
            }
            foreach ($row['payments'] as $p) {
                $attributes = collect($p)->only(['amount', 'payment_date', 'method', 'notes'])->all();
                $attributes['visit_procedure_id'] = isset($p['procedure_index']) && $p['procedure_index'] !== ''
                    ? $procedures[$i][(int) $p['procedure_index']]->id
                    : null;
                $visit->payments()->create($attributes);
                $summary['ordinary_payments']++;
            }
        }
        // All real treatment visits exist before optional receipt links are resolved.
        foreach ($data['visits'] as $i => $row) {
            if ($row['arrangement'] !== 'installment') {
                continue;
            }
            $p = $row['plan'];
            $paid = self::cents($p['downpayment']) + array_sum(array_map(fn ($r) => self::cents($r['amount']), $p['payments']));
            $balance = self::cents($p['total_cost']) - $paid;
            $plan = InstallmentPlan::create([
                'patient_id' => $patientId, 'visit_id' => $visits[$i]->id, 'service_id' => $row['procedures'][(int) $p['procedure_index']]['service_id'],
                'total_cost' => $p['total_cost'], 'downpayment' => $p['downpayment'], 'balance' => $balance / 100,
                'start_date' => $p['start_date'], 'is_open_contract' => $p['is_open_contract'],
                'months' => $p['is_open_contract'] ? 0 : $p['months'],
                'open_monthly_payment' => $p['is_open_contract'] ? ($p['open_monthly_payment'] ?? null) : null,
                'status' => $balance <= 0 ? InstallmentPlan::STATUS_FULLY_PAID : InstallmentPlan::STATUS_PARTIALLY_PAID,
            ]);
            $summary['installment_plans']++;
            if (self::cents($p['downpayment']) > 0) {
                $plan->payments()->create([
                    'visit_id' => null, 'month_number' => 0, 'amount' => $p['downpayment'],
                    'method' => $p['downpayment_method'], 'payment_date' => $p['downpayment_date'], 'notes' => 'Downpayment',
                ]);
                $summary['installment_payments']++;
            }
            foreach ($p['payments'] as $receipt) {
                $plan->payments()->create([
                    'visit_id' => isset($receipt['visit_index']) ? $visits[$receipt['visit_index']]->id : ($receipt['visit_id'] ?? null),
                    'month_number' => $receipt['month_number'], 'amount' => $receipt['amount'],
                    'method' => $receipt['method'], 'payment_date' => $receipt['payment_date'], 'notes' => $receipt['notes'] ?? null,
                ]);
                $summary['installment_payments']++;
            }
        }

        return $summary;
    }
}
