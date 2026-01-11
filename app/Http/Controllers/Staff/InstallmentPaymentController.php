<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstallmentPaymentController extends Controller
{
    private function safeReturn(?string $url): ?string
    {
        if (!$url) return null;

        $appUrl = rtrim(url('/'), '/');
        return Str::startsWith($url, $appUrl) ? $url : null;
    }

    private function findDownpaymentPayment(InstallmentPlan $plan): ?InstallmentPayment
    {
        $plan->loadMissing('payments');

        $down = (float)($plan->downpayment ?? 0);
        $planStartStr = $plan->start_date
            ? \Carbon\Carbon::parse($plan->start_date)->toDateString()
            : null;

        return $plan->payments->first(function ($p) use ($down, $planStartStr) {
            $m = (int)($p->month_number ?? -1);
            $notes = strtolower((string)($p->notes ?? ''));

            // NEW: month 0 = DP
            if ($m === 0) return true;

            // LEGACY: month 1 may be DP
            if ($m === 1) {
                if (str_contains($notes, 'downpayment')) return true;

                $amt = (float)($p->amount ?? 0);
                $pd  = $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->toDateString() : null;

                if ($down > 0 && abs($amt - $down) < 0.01 && $planStartStr && $pd === $planStartStr) {
                    return true;
                }
            }

            return false;
        });
    }

    private function ensureDownpaymentPayment(InstallmentPlan $plan): void
    {
        $plan->loadMissing('payments');

        $down = (float)($plan->downpayment ?? 0);
        if ($down <= 0) return;

        $dp = $this->findDownpaymentPayment($plan);
        if ($dp) return;

        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'visit_id'            => $plan->visit_id,
            'month_number'        => 0,
            'amount'              => $down,
            'method'              => 'Cash',
            'payment_date'        => $plan->start_date,
            'notes'               => 'Downpayment',
        ]);

        $plan->loadMissing('payments');
    }

    private function monthShift(InstallmentPlan $plan): int
    {
        $plan->loadMissing('payments');

        $dp = $this->findDownpaymentPayment($plan);
        if (!$dp) return 0;

        $hasMonth0 = $plan->payments->contains(fn($p) => (int)($p->month_number ?? -1) === 0);
        $isLegacyMonth1 = !$hasMonth0 && (int)($dp->month_number ?? -1) === 1;

        // If DP was stored as month 1 (legacy), shift by 1
        return $isLegacyMonth1 ? 1 : 0;
    }

    private function recomputePlan(InstallmentPlan $plan): InstallmentPlan
    {
        $plan->loadMissing('payments');

        $totalCost = (float)($plan->total_cost ?? 0);
        $down      = (float)($plan->downpayment ?? 0);

        $paymentsTotal = (float)$plan->payments->sum('amount');
        $hasDpRecord   = (bool)$this->findDownpaymentPayment($plan);

        // If DP record exists in payments, do NOT double-count plan.downpayment
        $paid = $paymentsTotal + ($hasDpRecord ? 0 : $down);

        $balance = max(0, $totalCost - $paid);
        $status  = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';

        $plan->balance = $balance;
        $plan->status  = $status;
        $plan->save();

        return $plan;
    }

    private function computeNextOpenPaymentNo(InstallmentPlan $plan, int $shift): int
    {
        // UI payment numbers are based on DB month_number minus shift.
        $used = $plan->payments
            ->filter(fn($p) => (int)($p->month_number ?? -1) >= (1 + $shift))
            ->map(fn($p) => (int)($p->month_number ?? 0) - $shift)
            ->filter(fn($n) => $n >= 1)
            ->unique();

        $n = 1;
        while ($used->contains($n)) $n++;
        return $n;
    }

    public function create(Request $request, InstallmentPlan $plan)
    {
        $plan->loadMissing(['patient', 'service', 'visit', 'payments']);

        $this->ensureDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        $shift  = $this->monthShift($plan);
        $isOpen = (bool)($plan->is_open_contract ?? false);

        if ($isOpen) {
            $nextMonth = $this->computeNextOpenPaymentNo($plan, $shift);

            $paidMonths = $plan->payments
                ->filter(fn($p) => (int)($p->month_number ?? -1) >= (1 + $shift))
                ->map(fn($p) => (int)($p->month_number ?? 0) - $shift)
                ->filter(fn($n) => $n >= 1)
                ->unique()
                ->sort()
                ->values();

            $maxMonths = null; // unlimited
            return view('staff.payments.installment.pay', compact('plan', 'paidMonths', 'nextMonth', 'maxMonths', 'isOpen'));
        }

        $maxMonths = max(0, (int)($plan->months ?? 0));
        if ($maxMonths < 1) {
            return redirect()
                ->route('staff.installments.show', $plan)
                ->with('success', 'No monthly installments configured for this plan.');
        }

        $paidMonths = $plan->payments
            ->filter(fn($p) => (int)($p->month_number ?? -1) >= (1 + $shift))
            ->map(fn($p) => (int)($p->month_number ?? 0) - $shift)
            ->filter(fn($m) => $m >= 1)
            ->unique()
            ->sort()
            ->values();

        $requestedMonth = (int)$request->query('month', 0);

        $nextMonth = null;
        if ($requestedMonth >= 1 && $requestedMonth <= $maxMonths && !$paidMonths->contains($requestedMonth)) {
            $nextMonth = $requestedMonth;
        } else {
            for ($i = 1; $i <= $maxMonths; $i++) {
                if (!$paidMonths->contains($i)) { $nextMonth = $i; break; }
            }
        }

        if ($nextMonth === null) {
            return redirect()
                ->route('staff.installments.show', $plan)
                ->with('success', 'This plan is already fully paid.');
        }

        $isOpen = false;
        return view('staff.payments.installment.pay', compact('plan', 'paidMonths', 'nextMonth', 'maxMonths', 'isOpen'));
    }

    public function store(Request $request, InstallmentPlan $plan)
    {
        $plan->loadMissing(['patient', 'visit', 'service', 'payments']);

        $isOpen = (bool)($plan->is_open_contract ?? false);

        // ✅ FIX: month_number should NOT be required for open contract
        $rules = [
            'month_number' => $isOpen ? 'nullable|integer|min:1' : 'required|integer|min:1',
            'amount'       => 'required|numeric|min:0',
            'method'       => 'required|string|max:50',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:2000',
        ];
        $request->validate($rules);

        $this->ensureDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        $shift = $this->monthShift($plan);

        if ($isOpen) {
            // ✅ Server enforces the next payment number
            $uiMonth = $this->computeNextOpenPaymentNo($plan, $shift);
        } else {
            $maxMonths = max(0, (int)($plan->months ?? 0));
            $uiMonth = (int)$request->month_number;

            if ($uiMonth < 1 || $uiMonth > $maxMonths) {
                return back()->withErrors('Invalid month selected.')->withInput();
            }
        }

        $dbMonth = $uiMonth + $shift;

        if ($plan->payments()->where('month_number', $dbMonth)->exists()) {
            return back()->withErrors('This payment number is already recorded.')->withInput();
        }

        $amount = (float)$request->amount;
        if ($amount > (float)($plan->balance ?? 0)) {
            return back()->withErrors('Amount exceeds the remaining balance.')->withInput();
        }

        $baseVisit  = $plan->visit;
        $visitDate  = $request->payment_date;
        $visitNotes = trim((string)$request->notes);

        if ($visitNotes === '') {
            $svc = $plan->service?->name;
            $label = $isOpen ? "Payment #{$uiMonth}" : "Month {$uiMonth}";
            $visitNotes = $svc
                ? "Installment payment ({$label}) - {$svc}"
                : "Installment payment ({$label})";
        }

        $visit = Visit::create([
            'patient_id'   => $plan->patient_id,
            'doctor_id'    => $baseVisit?->doctor_id,
            'dentist_name' => $baseVisit?->dentist_name,
            'visit_date'   => $visitDate,
            'status'       => 'completed',
            'notes'        => $visitNotes,
            'price'        => null,
        ]);

        if (!empty($plan->service_id)) {
            $visit->procedures()->create([
                'service_id'   => $plan->service_id,
                'tooth_number' => null,
                'surface'      => null,
                'shade'        => null,
                'notes'        => null,
                'price'        => 0,
            ]);
        }

        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'visit_id'            => $visit->id,
            'month_number'        => $dbMonth,
            'amount'              => $amount,
            'method'              => $request->method,
            'payment_date'        => $request->payment_date,
            'notes'               => $request->notes,
        ]);

        $this->recomputePlan($plan);

        return redirect()
            ->route('staff.installments.show', $plan->id)
            ->with('success', 'Installment payment recorded and a visit was created.');
    }

    public function edit(Request $request, InstallmentPlan $plan, InstallmentPayment $payment)
    {
        if ((int)$payment->installment_plan_id !== (int)$plan->id) abort(404);

        $plan->loadMissing(['patient', 'service', 'payments']);

        $this->ensureDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        $return = $this->safeReturn($request->query('return')) ?? route('staff.installments.show', $plan->id);

        return view('staff.payments.installment.edit-payment', compact('plan', 'payment', 'return'));
    }

    public function update(Request $request, InstallmentPlan $plan, InstallmentPayment $payment)
    {
        if ((int)$payment->installment_plan_id !== (int)$plan->id) abort(404);

        $request->validate([
            'amount'       => 'required|numeric|min:0',
            'method'       => 'required|string|max:50',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:2000',
            'return'       => 'nullable|string',
        ]);

        $plan->loadMissing('payments');

        $this->ensureDownpaymentPayment($plan);

        $totalCost = (float)($plan->total_cost ?? 0);
        $down      = (float)($plan->downpayment ?? 0);

        $paymentsTotal = (float)$plan->payments->sum('amount');
        $hasDpRecord   = (bool)$this->findDownpaymentPayment($plan);

        $old = (float)($payment->amount ?? 0);
        $new = (float)$request->amount;

        $paidWithoutThis = ($paymentsTotal - $old) + ($hasDpRecord ? 0 : $down);
        $newPaid = $paidWithoutThis + $new;

        if ($newPaid > $totalCost + 0.0001) {
            return back()->withErrors('Updated amount makes total paid exceed the plan total cost.')->withInput();
        }

        $payment->update([
            'amount'       => $new,
            'method'       => $request->method,
            'payment_date' => $request->payment_date,
            'notes'        => $request->notes,
        ]);

        $dp = $this->findDownpaymentPayment($plan);
        if ($dp && (int)$dp->id === (int)$payment->id) {
            $plan->downpayment = $new;
            $plan->save();
        }

        if (!empty($payment->visit_id)) {
            $visit = Visit::find($payment->visit_id);
            if ($visit) {
                $visit->visit_date = $request->payment_date;

                $n = trim((string)$request->notes);
                if ($n !== '') $visit->notes = $n;

                $visit->save();
            }
        }

        $this->recomputePlan($plan);

        $return = $this->safeReturn($request->input('return')) ?? route('staff.installments.show', $plan->id);

        return redirect($return)->with('success', 'Installment payment updated.');
    }
}
