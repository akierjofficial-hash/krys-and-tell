<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Visit;
use Illuminate\Http\Request;

class InstallmentPaymentController extends Controller
{
    /**
     * Show the pay form for an installment plan.
     */
    public function create(InstallmentPlan $plan)
    {
        $plan->loadMissing(['patient', 'service', 'visit', 'payments']);

        // For older plans: if there is a downpayment but no recorded Month 1 payment,
        // treat Month 1 as paid (so the user won't accidentally pay it twice).
        $paidMonths = $plan->payments->pluck('month_number')->filter()->values();
        if (!$paidMonths->contains(1) && (float) ($plan->downpayment ?? 0) > 0) {
            $paidMonths->push(1);
        }

        // Default select the next unpaid month.
        $nextMonth = null;
        for ($i = 1; $i <= (int) ($plan->months ?? 0); $i++) {
            if (!$paidMonths->contains($i)) {
                $nextMonth = $i;
                break;
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
     *
     * Recommendation B (default): Paying a month auto-creates a Visit record
     * so the clinic can record notes (e.g., braces wire adjustments, recementation).
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

        $plan->loadMissing(['patient', 'visit', 'service']);

        $monthNumber = (int) $request->month_number;

        if ($monthNumber > (int) ($plan->months ?? 0)) {
            return back()->withErrors('Invalid month selected.')->withInput();
        }

        // Prevent duplicate payments for the same month.
        $alreadyPaid = $plan->payments()->where('month_number', $monthNumber)->exists();
        if ($alreadyPaid) {
            return back()->withErrors('This month is already paid.')->withInput();
        }

        // Backward-compatibility: if an older plan has a downpayment recorded on the plan itself
        // (but month 1 was never inserted into installment_payments), do not allow re-paying month 1.
        if ($monthNumber === 1) {
            $hasMonth1 = $plan->payments()->where('month_number', 1)->exists();
            if (!$hasMonth1 && (float) ($plan->downpayment ?? 0) > 0) {
                return back()->withErrors('Downpayment is already recorded for this plan.')->withInput();
            }
        }

        // ----------
        // Auto-create visit for this monthly payment
        // ----------
        $baseVisit = $plan->visit; // may be null for some legacy plans

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

        // Optional: add a 0-priced procedure tied to the plan's service so the Visit shows a "Treatment" tag
        // but will NOT appear as an unpaid cash visit (because due will be 0).
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

        // Save installment payment and link the new visit.
        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'visit_id'            => $visit->id,
            'month_number'        => $monthNumber,
            'amount'              => $request->amount,
            'method'              => $request->method,
            'payment_date'        => $request->payment_date,
            'notes'               => $request->notes,
        ]);

        // ----------
        // Recompute balance/status
        // ----------
        $paymentsTotal = (float) $plan->payments()->sum('amount');

        // Backwards-compatible: if month 1 wasn't recorded, treat plan.downpayment as already paid.
        $hasMonth1Payment = $plan->payments()->where('month_number', 1)->exists();
        $totalPaid = $paymentsTotal + ($hasMonth1Payment ? 0 : (float) ($plan->downpayment ?? 0));

        $balance = max(0, (float) ($plan->total_cost ?? 0) - $totalPaid);
        $status = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';

        $plan->update([
            'balance' => $balance,
            'status'  => $status,
        ]);

        return redirect()
            ->route('staff.installments.show', $plan->id)
            ->with('success', 'Installment payment recorded and a visit was created.');
    }
}
