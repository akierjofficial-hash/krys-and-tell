<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Models\Visit;
use App\Models\Appointment;

class InstallmentPlanController extends Controller
{
    /**
     * Show all installment plans (optional: you are currently showing in payments.index)
     */
    public function index(Request $request)
    {
        $query = InstallmentPlan::with([
            'patient',
            'service',
            'visit.patient',
            'visit.procedures.service',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                // search patient
                $q->whereHas('patient', function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                // search service directly on plan
                ->orWhereHas('service', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                // search via visit procedures services
                ->orWhereHas('visit.procedures.service', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        $installments = $query->latest()->get();
        $cashPayments = collect(); // because payments.index expects both

        return view('payments.index', compact('installments', 'cashPayments'));
    }

    /**
     * Show create form (your payments.installment.create)
     */
    public function create()
    {
        // IMPORTANT: load procedures instead of services
        $visits = Visit::with(['patient', 'procedures.service'])->latest()->get();

        $appointments = Appointment::with(['patient', 'service'])
            ->whereNotNull('patient_id')
            ->whereNotNull('service_id')
            ->latest()
            ->get();

        return view('payments.installment.create', compact('visits', 'appointments'));
    }

    /**
     * Store new installment plan
     * NOTE: If you are already using PaymentController@storeInstallment, you can ignore this.
     */
    public function store(Request $request)
    {
        $request->validate([
            'visit_id'     => 'required|exists:visits,id',
            'total_cost'   => 'required|numeric|min:0',
            'downpayment'  => 'required|numeric|min:0|lte:total_cost',
            'months'       => 'required|integer|min:1',
            'start_date'   => 'required|date',
        ]);

        $visit = Visit::with(['patient', 'procedures.service'])->findOrFail($request->visit_id);

        $balance = $request->total_cost - $request->downpayment;
        $status  = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';

        $plan = InstallmentPlan::create([
            'visit_id'    => $visit->id,
            'patient_id'  => $visit->patient_id,
            // optionally set service_id if you want: first procedure’s service
            'service_id'  => optional($visit->procedures->first()?->service)->id,
            'total_cost'  => $request->total_cost,
            'downpayment' => $request->downpayment,
            'balance'     => $balance,
            'months'      => $request->months,
            'start_date'  => $request->start_date,
            'status'      => $status,
        ]);

        // Save DOWNPAYMENT as first payment (optional)
        if ($request->downpayment > 0) {
            InstallmentPayment::create([
                'installment_plan_id' => $plan->id,
                'month_number'        => 1,
                'amount'              => $request->downpayment,
                'method'              => 'Cash',
                'payment_date'        => $request->start_date,
            ]);
        }

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan created successfully!');
    }

    /**
     * Show installment receipt
     */
    public function show(InstallmentPlan $plan)
    {
        $plan->load([
            'patient',
            'service',
            'visit.patient',
            'visit.procedures.service',
            'payments',
        ]);

        return view('payments.installment.show', compact('plan'));
    }

    /**
     * Add installment payment
     * NOTE: Your previous version used wrong column names ('date' instead of 'payment_date').
     */
    public function addPayment(Request $request, $id)
    {
        $plan = InstallmentPlan::findOrFail($id);

        $request->validate([
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:1|max:' . $plan->balance,
            'method'       => 'nullable|string',
        ]);

        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'payment_date'        => $request->payment_date,
            'amount'              => $request->amount,
            'method'              => $request->method ?? 'Cash',
        ]);

        // recompute balance + status safely
        $paidTotal = $plan->payments()->sum('amount');
        $balance   = max(($plan->total_cost - $paidTotal), 0);

        $plan->balance = $balance;
        $plan->status  = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';
        $plan->save();

        return back()->with('success', 'Payment added successfully.');
    }

    /**
     * Edit installment plan
     */
    public function edit(InstallmentPlan $plan)
    {
        $visits = Visit::with(['patient', 'procedures.service'])->latest()->get();

        $appointments = Appointment::with(['patient', 'service'])->latest()->get();

        $plan->load([
            'patient',
            'service',
            'visit.patient',
            'visit.procedures.service',
        ]);

        return view('payments.installment.edit', compact('plan', 'visits', 'appointments'));
    }

    /**
     * ✅ Update installment plan (FIXED)
     * - No "services" relationship used
     * - Recalculates balance + status
     * - Uses route model binding so it won’t just refresh
     */
    public function update(Request $request, InstallmentPlan $plan)
    {
        $request->validate([
            'total_cost'  => 'required|numeric|min:0',
            'downpayment' => 'required|numeric|min:0|lte:total_cost',
            'months'      => 'required|integer|min:1',
            'start_date'  => 'required|date',
        ]);

        // Recompute balance based on total_cost and downpayment
        $balance = $request->total_cost - $request->downpayment;
        $status  = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';

        $plan->update([
            'total_cost'  => $request->total_cost,
            'downpayment' => $request->downpayment,
            'balance'     => max($balance, 0),
            'months'      => $request->months,
            'start_date'  => $request->start_date,
            'status'      => $status,
        ]);

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan updated successfully!');
    }

    /**
     * Delete installment plan
     */
    public function destroy(InstallmentPlan $plan)
    {
        $plan->delete();

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment deleted successfully');
    }
}
