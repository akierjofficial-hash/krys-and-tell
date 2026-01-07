<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Models\Visit;
use App\Models\Appointment;

class InstallmentPlanController extends Controller
{
    /**
     * ✅ Single source of truth:
     * Balance + status are computed from total_cost - sum(payments.amount)
     * (Month 1 downpayment is stored as a payment row, so no double-counting.)
     */
    private function recomputePlan(InstallmentPlan $plan): InstallmentPlan
    {
        $plan->loadMissing('payments');

        $totalCost = (float) ($plan->total_cost ?? 0);
        $paidTotal = (float) $plan->payments->sum('amount');

        $balance = max(0, $totalCost - $paidTotal);
        $status  = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';

        $plan->balance = $balance;
        $plan->status  = $status;
        $plan->save();

        return $plan;
    }

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
                $q->whereHas('patient', function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('visit.procedures.service', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        $installments = $query->latest()->get();

        // keep the payments.index view happy
        $cashPayments = collect();

        return view('staff.payments.index', compact('installments', 'cashPayments'));
    }

    public function create()
    {
        $visits = Visit::with(['patient', 'procedures.service'])->latest()->get();

        $appointments = Appointment::with(['patient', 'service'])
            ->whereNotNull('patient_id')
            ->whereNotNull('service_id')
            ->latest()
            ->get();

        return view('staff.payments.installment.create', compact('visits', 'appointments'));
    }

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

        $plan = InstallmentPlan::create([
            'visit_id'    => $visit->id,
            'patient_id'  => $visit->patient_id,
            'service_id'  => optional($visit->procedures->first()?->service)->id,
            'total_cost'  => (float) $request->total_cost,
            'downpayment' => (float) $request->downpayment,
            'months'      => (int) $request->months,
            'start_date'  => $request->start_date,
            'status'      => 'Partially Paid', // will be recomputed
            'balance'     => 0, // will be recomputed
        ]);

        // ✅ Store downpayment as Month 1 payment record (if > 0)
        if ((float) $request->downpayment > 0) {
            InstallmentPayment::create([
                'installment_plan_id' => $plan->id,
                'month_number'        => 1,
                'amount'              => (float) $request->downpayment,
                'method'              => 'Cash',
                'payment_date'        => $request->start_date,
                'notes'               => 'Downpayment (Month 1)',
                'visit_id'            => $plan->visit_id, // optional link
            ]);
        }

        // ✅ Compute correct balance/status
        $this->recomputePlan($plan);

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan created successfully!');
    }

    public function show(InstallmentPlan $plan)
    {
        $plan->load([
            'patient',
            'service',
            'visit.patient',
            'visit.procedures.service',
            'payments',
        ]);

        // ✅ keep displayed balance correct even if old data was wrong
        $this->recomputePlan($plan);

        return view('staff.payments.installment.show', compact('plan'));
    }

    /**
     * OPTIONAL/LEGACY: If you still use this route somewhere, make it consistent:
     * month_number is required for installment payments UX (otherwise it breaks your month table).
     */
    public function addPayment(Request $request, $id)
    {
        $plan = InstallmentPlan::with('payments')->findOrFail($id);

        $request->validate([
            'month_number' => 'required|integer|min:1|max:' . (int)($plan->months ?? 1),
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:1',
            'method'       => 'nullable|string|max:50',
        ]);

        // Prevent duplicate month
        if ($plan->payments()->where('month_number', (int)$request->month_number)->exists()) {
            return back()->withErrors('This month is already paid.')->withInput();
        }

        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'month_number'        => (int) $request->month_number,
            'payment_date'        => $request->payment_date,
            'amount'              => (float) $request->amount,
            'method'              => $request->method ?? 'Cash',
        ]);

        $this->recomputePlan($plan);

        return back()->with('success', 'Payment added successfully.');
    }

    public function edit(InstallmentPlan $plan)
    {
        $visits = Visit::with(['patient', 'procedures.service'])->latest()->get();
        $appointments = Appointment::with(['patient', 'service'])->latest()->get();

        $plan->load([
            'patient',
            'service',
            'visit.patient',
            'visit.procedures.service',
            'payments',
        ]);

        // keep correct
        $this->recomputePlan($plan);

        return view('staff.payments.installment.edit', compact('plan', 'visits', 'appointments'));
    }

    public function update(Request $request, InstallmentPlan $plan)
    {
        $request->validate([
            'total_cost'  => 'required|numeric|min:0',
            'downpayment' => 'required|numeric|min:0|lte:total_cost',
            'months'      => 'required|integer|min:1',
            'start_date'  => 'required|date',
        ]);

        $plan->loadMissing('payments');

        $newTotal = (float) $request->total_cost;
        $newDown  = (float) $request->downpayment;

        // ✅ Update plan core fields (balance/status recomputed after)
        $plan->update([
            'total_cost'  => $newTotal,
            'downpayment' => $newDown,
            'months'      => (int) $request->months,
            'start_date'  => $request->start_date,
        ]);

        // ✅ Keep Month 1 payment synced with downpayment (best UX)
        $month1 = $plan->payments()->where('month_number', 1)->first();

        if ($newDown > 0) {
            if ($month1) {
                $month1->update([
                    'amount'       => $newDown,
                    'payment_date' => $request->start_date,
                    'method'       => $month1->method ?? 'Cash',
                    'notes'        => $month1->notes ?: 'Downpayment (Month 1)',
                ]);
            } else {
                InstallmentPayment::create([
                    'installment_plan_id' => $plan->id,
                    'visit_id'            => $plan->visit_id,
                    'month_number'        => 1,
                    'amount'              => $newDown,
                    'method'              => 'Cash',
                    'payment_date'        => $request->start_date,
                    'notes'               => 'Downpayment (Month 1)',
                ]);
            }
        } else {
            // If downpayment becomes 0, delete month 1 ONLY if it looks like the downpayment record.
            // (avoids deleting real month 1 payment if you used it differently)
            if ($month1 && (stripos((string)$month1->notes, 'Downpayment') !== false)) {
                $month1->delete();
            }
        }

        // ✅ Recompute plan using actual payments total
        $this->recomputePlan($plan);

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan updated successfully!');
    }

    public function destroy(InstallmentPlan $plan)
    {
        $plan->delete();

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment deleted successfully');
    }
}
