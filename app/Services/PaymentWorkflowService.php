<?php

namespace App\Services;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Payment;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentWorkflowService
{
    public function __construct(private FinancialService $finance) {}

    public function record(array $data): Payment|InstallmentPayment
    {
        return DB::transaction(function () use ($data) {
            if ($existing = Payment::where('submission_token', $data['submission_token'])->first()) return $existing;
            if ($existing = InstallmentPayment::where('submission_token', $data['submission_token'])->first()) return $existing;

            if ($data['target_type'] === 'visit') {
                $visit = Visit::with(['procedures', 'payments'])->lockForUpdate()->findOrFail($data['target_id']);
                if ((int) $visit->patient_id !== (int) $data['patient_id']) $this->invalid('target_id', 'That visit does not belong to the selected patient.');
                if ($visit->installmentPlan()->exists()) $this->invalid('target_id', 'Use the installment plan for this treatment.');
                $balance = $this->finance->visitBalance($visit);
                $this->validateAmount((float) $data['amount'], $balance);
                $payment = Payment::create([
                    'visit_id' => $visit->id, 'amount' => $data['amount'], 'method' => $data['method'],
                    'payment_date' => $data['payment_date'], 'notes' => $data['notes'] ?? null,
                    'submission_token' => $data['submission_token'],
                ]);
                $visit->load('payments');
                $this->finance->syncVisitStatus($visit);
                return $payment;
            }

            $plan = InstallmentPlan::with('payments')->lockForUpdate()->findOrFail($data['target_id']);
            if ((int) $plan->patient_id !== (int) $data['patient_id']) $this->invalid('target_id', 'That plan does not belong to the selected patient.');
            if ($plan->status === InstallmentPlan::STATUS_COMPLETED) $this->invalid('target_id', 'This plan is closed. Reopen it before recording a payment.');
            if (!$plan->hasUnknownTotal()) {
                $balance = $this->finance->planBalance($plan);
                $this->validateAmount((float) $data['amount'], $balance);
            } elseif ((float) $data['amount'] <= 0) {
                $this->invalid('amount', 'Enter a positive receipt amount.');
            }
            $payment = InstallmentPayment::create([
                'installment_plan_id' => $plan->id, 'visit_id' => null,
                'month_number' => $this->finance->nextPlanMonth($plan), 'amount' => $data['amount'],
                'method' => $data['method'], 'payment_date' => $data['payment_date'],
                'notes' => $data['notes'] ?? null, 'submission_token' => $data['submission_token'],
            ]);
            $this->finance->recomputePlan($plan);
            return $payment;
        }, 3);
    }

    private function validateAmount(float $amount, float $balance): void
    {
        if ($balance <= 0) $this->invalid('target_id', 'This item is already fully paid.');
        if ($amount > $balance + .001) $this->invalid('amount', 'Payment cannot exceed the remaining balance.');
    }

    private function invalid(string $key, string $message): never
    {
        throw ValidationException::withMessages([$key => $message]);
    }
}
