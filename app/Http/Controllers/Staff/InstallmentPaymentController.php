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
    /**
     * Keep redirects safe (no open redirect).
     */
    private function safeReturn(?string $url): ?string
    {
        if (!$url) return null;

        // allow only same-app absolute URLs
        $appUrl = rtrim(url('/'), '/');
        if (Str::startsWith($url, $appUrl)) return $url;

        return null;
    }

    /**
     * ✅ Recompute plan balance/status correctly (avoid double-counting downpayment).
     * - If Month 1 payment exists, do NOT add plan.downpayment again.
     * - If Month 1 payment does NOT exist, treat plan.downpayment as already paid (legacy support).
     */
    private function recomputePlan(InstallmentPlan $plan): InstallmentPlan
    {
        $plan->loadMissing('payments');

        $totalCost   = (float) ($plan->total_cost ?? 0);
        $downpayment = (float) ($plan->downpayment ?? 0);

        $paymentsTotal = (float) $plan->payments->sum('amount');
        $hasMonth1Payment = $plan->payments->contains(function ($p) {
            return (int) ($p->month_number ?? 0) === 1;
        });

        $paidAmount = $paymentsTotal + ($hasMonth1Payment ? 0 : $downpayment);
        $balance = max(0, $totalCost - $paidAmount);

        $status = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';

        // keep DB consistent (so pay.blade.php which uses $plan->balance shows correct value)
        $plan->balance = $balance;
        $plan->status = $status;
        $plan->save();

        return $plan;
    }

    /**
     * Show the pay form for an installment plan.
     * Supports: ?month=3 to preselect month 3 (best UX from table row).
     */
    public function create(Request $request, InstallmentPlan $plan)
    {
        $plan->loadMissing(['patient', 'service', 'visit', 'payments']);

        // ✅ fix plan balance/status when opening pay form (handles older wrong data)
        $this->recomputePlan($plan);

        // Paid months
        $paidMonths = $plan->payments->pluck('month_number')->filter()->values();

        // Legacy: if downpayment exists but month 1 payment record doesn't exist, treat month 1 as paid
        if (!$paidMonths->contains(1) && (float) ($plan->downpayment ?? 0) > 0) {
            $paidMonths->push(1);
        }

        // Requested month (from table row "Pay" button)
        $requestedMonth = (int) $request->query('month', 0);

        // Default select month:
        // - if requested month is valid & unpaid -> use it
        // - else use next unpaid month
        $nextMonth = null;

        if ($requestedMonth >= 1 && $requestedMonth <= (int) ($plan->months ?? 0) && !$paidMonths->contains($requestedMonth)) {
            $nextMonth = $requestedMonth;
        } else {
            for ($i = 1; $i <= (int) ($plan->months ?? 0); $i++) {
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

    /**
     * Store a monthly installment payment.
     * Auto-creates a Visit record for notes.
     */
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

        // ✅ Ensure plan is consistent before validating overpay
        $this->recomputePlan($plan);

        $monthNumber = (int) $request->month_number;

        if ($monthNumber > (int) ($plan->months ?? 0)) {
            return back()->withErrors('Invalid month selected.')->withInput();
        }

        // Prevent duplicate payments for the same month
        if ($plan->payments()->where('month_number', $monthNumber)->exists()) {
            return back()->withErrors('This month is already paid.')->withInput();
        }

        // Legacy: if downpayment exists but no month 1 record, block paying month 1 again
        if ($monthNumber === 1) {
            $hasMonth1 = $plan->payments()->where('month_number', 1)->exists();
            if (!$hasMonth1 && (float) ($plan->downpayment ?? 0) > 0) {
                return back()->withErrors('Downpayment is already recorded for this plan.')->withInput();
            }
        }

        // ✅ Server-side anti-overpay check
        $amount = (float) $request->amount;
        if ($amount > (float) ($plan->balance ?? 0)) {
            return back()->withErrors('Amount exceeds the remaining balance.')->withInput();
        }

        // Auto-create visit
        $baseVisit = $plan->visit;

        $visitDate = $request->payment_date;
        $visitNotes = trim((string) $request->notes);
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

        // Optional: add a 0-priced procedure so visit shows treatment tag
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

        // Save payment
        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'visit_id'            => $visit->id,
            'month_number'        => $monthNumber,
            'amount'              => $amount,
            'method'              => $request->method,
            'payment_date'        => $request->payment_date,
            'notes'               => $request->notes,
        ]);

        // ✅ Recompute plan after insert
        $this->recomputePlan($plan);

        return redirect()
            ->route('staff.installments.show', $plan->id)
            ->with('success', 'Installment payment recorded and a visit was created.');
    }

    /**
     * ✅ NEW: Edit an installment payment (per month).
     */
    public function edit(Request $request, InstallmentPlan $plan, InstallmentPayment $payment)
    {
        // Ensure the payment belongs to the plan
        if ((int) $payment->installment_plan_id !== (int) $plan->id) {
            abort(404);
        }

        $plan->loadMissing(['patient', 'service']);
        $payment->loadMissing('visit');

        // keep plan consistent
        $this->recomputePlan($plan);

        $return = $this->safeReturn($request->query('return')) ?? route('staff.installments.show', $plan->id);

        return view('staff.payments.installment.edit-payment', compact('plan', 'payment', 'return'));
    }

    /**
     * ✅ NEW: Update an installment payment (per month) + recompute balance.
     */
    public function update(Request $request, InstallmentPlan $plan, InstallmentPayment $payment)
    {
        // Ensure the payment belongs to the plan
        if ((int) $payment->installment_plan_id !== (int) $plan->id) {
            abort(404);
        }

        $request->validate([
            'amount'       => 'required|numeric|min:0',
            'method'       => 'required|string|max:50',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:2000',
            'return'       => 'nullable|string',
        ]);

        $plan->loadMissing('payments');

        // Calculate "balance without this payment", then ensure new amount won't exceed total cost
        $totalCost = (float) ($plan->total_cost ?? 0);
        $downpayment = (float) ($plan->downpayment ?? 0);

        $payments = $plan->payments->keyBy('id');
        $paymentsTotal = (float) $plan->payments->sum('amount');

        $hasMonth1Payment = $plan->payments->contains(function ($p) {
            return (int) ($p->month_number ?? 0) === 1;
        });

        // remove current payment from totals, add new amount
        $old = (float) ($payment->amount ?? 0);
        $new = (float) $request->amount;

        $paidWithoutThis = ($paymentsTotal - $old) + ($hasMonth1Payment ? 0 : $downpayment);
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

        // If this payment is month 1, keep plan.downpayment aligned (best UX)
        if ((int) ($payment->month_number ?? 0) === 1) {
            $plan->downpayment = $new;
            $plan->save();
        }

        // Update linked visit (if exists)
        if (!empty($payment->visit_id)) {
            $visit = Visit::find($payment->visit_id);
            if ($visit) {
                $visit->visit_date = $request->payment_date;

                $n = trim((string) $request->notes);
                if ($n !== '') {
                    $visit->notes = $n;
                }

                $visit->save();
            }
        }

        // Recompute plan after update
        $this->recomputePlan($plan);

        $return = $this->safeReturn($request->input('return')) ?? route('staff.installments.show', $plan->id);

        return redirect($return)->with('success', 'Installment payment updated.');
    }
}
