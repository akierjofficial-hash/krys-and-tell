<?php

namespace App\Services;

use App\Models\InstallmentPlan;
use Carbon\CarbonImmutable;

class OpenMonthlyContractService
{
    public function details(InstallmentPlan $plan, CarbonImmutable|string|null $asOf = null): array
    {
        $plan->loadMissing('payments');
        $today = $asOf instanceof CarbonImmutable ? $asOf : CarbonImmutable::parse($asOf ?: 'now', config('app.timezone'));
        $cutoff = $today->startOfDay();
        if ($plan->ended_at && $plan->ended_at->toDateString() < $cutoff->toDateString()) {
            $cutoff = CarbonImmutable::parse($plan->ended_at->toDateString(), config('app.timezone'));
        }
        $monthly = (float) $plan->open_monthly_payment;
        $first = $plan->first_due_date
            ? CarbonImmutable::parse($plan->first_due_date->toDateString(), config('app.timezone')) : null;
        $downpayment = app(FinancialService::class)->downpaymentPayment($plan);
        $receipts = $plan->payments->filter(fn ($payment) => $payment->payment_date
            && $payment->payment_date->toDateString() <= $today->toDateString());
        $collected = round((float) $receipts->sum('amount'), 2);
        $monthlyPaid = round((float) $receipts->reject(fn ($payment) => $downpayment && $payment->is($downpayment))->sum('amount'), 2);
        $dueCount = 0;
        if ($first && $monthly > 0) {
            for ($number = 0; ; $number++) {
                $due = $first->addMonthsNoOverflow($number);
                if ($due->greaterThan($cutoff)) break;
                $dueCount++;
            }
        }
        $covered = $monthly > 0 ? (int) floor(($monthlyPaid + 0.00001) / $monthly) : 0;
        // Apply monthly receipts to the oldest monthly obligation first. A partial
        // payment keeps that month's due date visible; an advance moves it forward.
        $nextDue = $first && !$plan->ended_at && $monthly > 0
            ? $first->addMonthsNoOverflow($covered)->toDateString() : null;

        return [
            'collected' => $collected,
            'monthly_amount' => $monthly,
            'monthly_received' => $monthlyPaid,
            'due_count' => $dueCount,
            'unpaid_due' => round(max(0, $dueCount * $monthly - $monthlyPaid), 2),
            'next_due_date' => $nextDue,
            'incomplete' => !$first || $monthly <= 0 || ((float) $plan->downpayment > 0 && !$downpayment),
        ];
    }
}
