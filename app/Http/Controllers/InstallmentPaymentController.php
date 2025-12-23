<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\DB;

class InstallmentPaymentController extends Controller
{

    public function create(InstallmentPlan $plan)
    {
        $plan->load(['payments', 'patient']);

        return view('payments.installment.pay', compact('plan'));
    }

    
    public function store(Request $request, InstallmentPlan $plan)
    {
        $request->validate([
            'month_number' => 'required|integer|min:1|max:' . $plan->months,
            'amount'       => 'required|numeric|min:1',
            'method'       => 'required|string',
        ]);

        if ($plan->payments()->where('month_number', $request->month_number)->exists()) {
            return back()->withErrors([
                'month_number' => 'This month already has a payment.'
            ])->withInput();
        }

        DB::transaction(function () use ($request, $plan) {

            $plan->payments()->create([
                'month_number' => $request->month_number,
                'amount'       => $request->amount,
                'method'       => $request->method,
                'payment_date' => now(),
            ]);

            // ✅ Recompute totals
            $paymentsTotal = $plan->payments()->sum('amount');
            $totalPaid     = $plan->downpayment + $paymentsTotal;
            $remaining     = $plan->total_cost - $totalPaid;

            // ✅ Update plan status
            $plan->update([
                'balance' => max($remaining, 0),
                'status'  => $remaining <= 0
                    ? 'Fully Paid'
                    : 'Partially Paid',
            ]);
        });

        return redirect()
            ->route('installments.show', $plan->id)
            ->with('success', 'Installment payment recorded successfully.');
    }
    
}
