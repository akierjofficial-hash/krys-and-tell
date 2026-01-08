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

    /**
     * NEW rule:
     * - Downpayment is month_number = 0 payment record
     * Legacy safety:
     * - If no DP record exists, count plan.downpayment once
     */
    private function hasDownpaymentRecord(InstallmentPlan $plan): bool
    {
        $plan->loadMissing('payments');

        return $plan->payments->contains(function ($p) {
            $m = (int)($p->month_number ?? -1);
            $notes = strtolower((string)($p->notes ?? ''));

            if ($m === 0) return true; // new
            if ($m === 1 && str_contains($notes, 'downpayment')) return true; // legacy
            return false;
        });
    }

    private function ensureDownpaymentPayment(InstallmentPlan $plan): void
    {
        $plan->loadMissing('payments');

        $down = (float)($plan->downpayment ?? 0);
        if ($down <= 0) return;

        $hasMonth0 = $plan->payments->contains(fn ($p) => (int)($p->month_number ?? -1) === 0);
        if ($hasMonth0) return;

        $hasLegacyDp = $plan->payments->contains(function ($p) {
            return (int)($p->month_number ?? -1) === 1
                && str_contains(strtolower((string)($p->notes ?? '')), 'downpayment');
        });
        if ($hasLegacyDp) return;

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

    private function recomputePlan(InstallmentPlan $plan): InstallmentPlan
    {
        $plan->loadMissing('payments');

        $totalCost = (float)($plan->total_cost ?? 0);
        $down      = (float)($plan->downpayment ?? 0);

        $paymentsTotal = (float)$plan->payments->sum('amount');
        $hasDpRecord   = $this->hasDownpaymentRecord($plan);

        $paid = $paymentsTotal + ($hasDpRecord ? 0 : $down);

        $balance = max(0, $totalCost - $paid);
        $status  = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';

        $plan->balance = $balance;
        $plan->status  = $status;
        $plan->save();

        return $plan;
    }

    /**
     * Pay form: months are ONLY 1..plan.months (downpayment is month 0 and not selectable here)
     */
    public function create(Request $request, InstallmentPlan $plan)
    {
        $plan->loadMissing(['patient', 'service', 'visit', 'payments']);

        $this->ensureDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        // ✅ Only monthly months (>=1). Month 0 DP excluded automatically.
        $paidMonths = $plan->payments
            ->pluck('month_number')
            ->filter(fn ($m) => (int)$m >= 1)
            ->values();

        $requestedMonth = (int)$request->query('month', 0);
        $maxMonths = (int)($plan->months ?? 0);

        $nextMonth = null;

        if ($requestedMonth >= 1 && $requestedMonth <= $maxMonths && !$paidMonths->contains($requestedMonth)) {
            $nextMonth = $requestedMonth;
        } else {
            for ($i = 1; $i <= $maxMonths; $i++) {
                if (!$paidMonths->contains($i)) {
                    $nextMonth = $i;
                    break;
                }
            }
        }

        if ($nextMonth === null) {
            return redirect()
                ->route('staff.installments.show', $plan)
                ->with('success', 'This plan is already fully paid.');
        }

        return view('staff.payments.installment.pay', compact('plan', 'paidMonths', 'nextMonth'));
    }

    public function store(Request $request, InstallmentPlan $plan)
    {
        $request->validate([
            'month_number' => 'required|integer|min:1',
            'amount'       => 'required|numeric|min:0',
            'method'       => 'required|string|max:50',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:2000',
        ]);

        $plan->loadMissing(['patient', 'visit', 'service', 'payments']);

        $this->ensureDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        $monthNumber = (int)$request->month_number;
        $maxMonths   = (int)($plan->months ?? 0);

        if ($monthNumber > $maxMonths) {
            return back()->withErrors('Invalid month selected.')->withInput();
        }

        if ($plan->payments()->where('month_number', $monthNumber)->exists()) {
            return back()->withErrors('This month is already paid.')->withInput();
        }

        $amount = (float)$request->amount;

        if ($amount > (float)($plan->balance ?? 0)) {
            return back()->withErrors('Amount exceeds the remaining balance.')->withInput();
        }

        // Auto-create visit
        $baseVisit = $plan->visit;

        $visitDate = $request->payment_date;
        $visitNotes = trim((string)$request->notes);
        if ($visitNotes === '') {
            $svc = $plan->service?->name;
            $visitNotes = $svc
                ? "Installment payment (Month {$monthNumber}) - {$svc}"
                : "Installment payment (Month {$monthNumber})";
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
            'month_number'        => $monthNumber,
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

        $totalCost = (float)($plan->total_cost ?? 0);

        $paymentsTotal = (float)$plan->payments->sum('amount');

        $old = (float)($payment->amount ?? 0);
        $new = (float)$request->amount;

        // paid without this payment
        $paidWithoutThis = $paymentsTotal - $old;

        // new total paid
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

        // ✅ If this is Downpayment (month 0), keep plan.downpayment aligned
        if ((int)($payment->month_number ?? -1) === 0) {
            $plan->downpayment = $new;
            $plan->save();
        }

        // Update linked visit (if exists)
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
