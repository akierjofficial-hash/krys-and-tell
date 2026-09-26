<?php

namespace App\Services;

use Carbon\CarbonInterface;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Payment;
use App\Models\Visit;

class FinancialService
{
    public function collectedOn(CarbonInterface|string $date): float
    {
        $day = $date instanceof CarbonInterface ? $date->toDateString() : $date;

        return (float) Payment::whereDate('payment_date', $day)->sum('amount')
            + (float) InstallmentPayment::whereDate('payment_date', $day)->sum('amount');
    }

    public function collectedBetween(CarbonInterface|string $from, CarbonInterface|string $to): float
    {
        $start = $from instanceof CarbonInterface ? $from->toDateString() : $from;
        $end = $to instanceof CarbonInterface ? $to->toDateString() : $to;

        return (float) Payment::whereBetween('payment_date', [$start, $end])->sum('amount')
            + (float) InstallmentPayment::whereBetween('payment_date', [$start, $end])->sum('amount');
    }

    public function visitCharge(Visit $visit): float
    {
        if ($visit->price !== null) return (float) $visit->price;
        $visit->loadMissing('procedures');
        return (float) $visit->procedures->sum(fn ($row) => (float) ($row->price ?? 0));
    }

    public function visitPaid(Visit $visit): float
    {
        $visit->loadMissing('payments');
        return (float) $visit->payments->sum(fn ($row) => (float) $row->amount);
    }

    public function visitBalance(Visit $visit): float
    {
        return max(0, round($this->visitCharge($visit) - $this->visitPaid($visit), 2));
    }

    public function downpaymentPayment(InstallmentPlan $plan): ?InstallmentPayment
    {
        $plan->loadMissing('payments');
        $start = $plan->start_date?->toDateString();
        $down = (float) $plan->downpayment;
        return $plan->payments->first(function ($payment) use ($start, $down) {
            if ((int) $payment->month_number === 0) return true;
            if ((int) $payment->month_number !== 1) return false;
            if (str_contains(strtolower((string) $payment->notes), 'downpayment')) return true;
            return $down > 0 && abs((float) $payment->amount - $down) < .01
                && $start && $payment->payment_date?->toDateString() === $start;
        });
    }

    public function planPaid(InstallmentPlan $plan): float
    {
        $plan->loadMissing('payments');
        $payments = (float) $plan->payments->sum(fn ($row) => (float) $row->amount);
        return $payments + ($this->downpaymentPayment($plan) ? 0 : (float) $plan->downpayment);
    }

    public function planBalance(InstallmentPlan $plan): float
    {
        return max(0, round((float) $plan->total_cost - $this->planPaid($plan), 2));
    }

    public function recomputePlan(InstallmentPlan $plan): InstallmentPlan
    {
        $plan->load('payments');
        $paid = $this->planPaid($plan);
        $balance = max(0, round((float) $plan->total_cost - $paid, 2));
        $plan->balance = $balance;
        if ($plan->status !== InstallmentPlan::STATUS_COMPLETED) {
            $plan->status = $balance <= 0 ? InstallmentPlan::STATUS_FULLY_PAID
                : ($paid > 0 ? InstallmentPlan::STATUS_PARTIALLY_PAID : InstallmentPlan::STATUS_PENDING);
        }
        $plan->save();
        return $plan;
    }

    public function nextPlanMonth(InstallmentPlan $plan): int
    {
        $plan->loadMissing('payments');
        $dp = $this->downpaymentPayment($plan);
        $shift = $dp && (int) $dp->month_number === 1
            && !$plan->payments->contains(fn ($row) => (int) $row->month_number === 0) ? 1 : 0;
        $used = $plan->payments->reject(fn ($row) => $dp && $row->is($dp))
            ->map(fn ($row) => (int) $row->month_number - $shift)->filter(fn ($n) => $n > 0);
        $number = 1;
        while ($used->contains($number)) $number++;
        return $number + $shift;
    }

    public function syncVisitStatus(Visit $visit): void
    {
        if ($visit->installmentPlan()->exists()) {
            $visit->update(['status' => 'installment']);
            return;
        }
        $visit->update(['status' => $this->visitBalance($visit) <= 0 ? 'completed' : 'partial']);
    }

    public function summary(): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $receivedToday = $this->collectedOn($today);
        $receivedMonth = $this->collectedBetween($monthStart, $monthEnd);

        $visits = Visit::with(['procedures', 'payments'])->whereDoesntHave('installmentPlan')->get();
        $plans = InstallmentPlan::with('payments')->get();
        return [
            'today' => $receivedToday,
            'month' => $receivedMonth,
            'outstanding' => $visits->sum(fn ($visit) => $this->visitBalance($visit))
                + $plans->sum(fn ($plan) => $this->planBalance($plan)),
            'active_plans' => $plans->filter(fn ($plan) => $this->planBalance($plan) > 0
                && $plan->status !== InstallmentPlan::STATUS_COMPLETED)->count(),
        ];
    }
}
