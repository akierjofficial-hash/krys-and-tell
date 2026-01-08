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
     * Detect downpayment record:
     * - NEW: month_number = 0
     * - LEGACY: month_number = 1 + notes contains "downpayment"
     */
    private function hasDownpaymentRecord(InstallmentPlan $plan): bool
    {
        $plan->loadMissing('payments');

        return $plan->payments->contains(function ($p) {
            $m = (int)($p->month_number ?? -1);
            $notes = strtolower((string)($p->notes ?? ''));

            if ($m === 0) return true; // ✅ new DP record
            if ($m === 1 && str_contains($notes, 'downpayment')) return true; // ✅ legacy DP
            return false;
        });
    }

    /**
     * Ensure downpayment payment row exists as month_number=0 (NEW format).
     * - If legacy DP exists (month 1 "downpayment"), we DO NOT create month 0 to avoid double.
     */
    private function ensureDownpaymentPayment(InstallmentPlan $plan): void
    {
        $plan->loadMissing('payments');

        $down = (float)($plan->downpayment ?? 0);
        if ($down <= 0) return;

        // If new DP exists, ok
        $hasMonth0 = $plan->payments->contains(fn ($p) => (int)($p->month_number ?? -1) === 0);
        if ($hasMonth0) return;

        // If legacy DP exists, do nothing (migration will handle shifting)
        $hasLegacyDp = $plan->payments->contains(function ($p) {
            return (int)($p->month_number ?? -1) === 1
                && str_contains(strtolower((string)($p->notes ?? '')), 'downpayment');
        });
        if ($hasLegacyDp) return;

        // Create month 0 DP record
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

    /**
     * Single source of truth:
     * balance/status = total_cost - paid
     * paid = sum(payments.amount) + (downpayment only if no DP payment exists)
     */
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
     * Keep month 0 DP payment synced when plan is edited.
     */
    private function syncDownpaymentPayment(InstallmentPlan $plan): void
    {
        $plan->loadMissing('payments');

        $down = (float)($plan->downpayment ?? 0);

        // If legacy DP exists (month 1 downpayment), don't create month 0 here
        $hasLegacyDp = $plan->payments->contains(function ($p) {
            return (int)($p->month_number ?? -1) === 1
                && str_contains(strtolower((string)($p->notes ?? '')), 'downpayment');
        });

        $dp = $plan->payments()->where('month_number', 0)->first();

        if ($down <= 0) {
            if ($dp) $dp->delete();
            return;
        }

        if ($hasLegacyDp && !$dp) {
            // leave it for migration to avoid duplicates
            return;
        }

        $payload = [
            'visit_id'     => $plan->visit_id,
            'month_number' => 0,
            'amount'       => $down,
            'method'       => $dp?->method ?? 'Cash',
            'payment_date' => $plan->start_date,
            'notes'        => $dp?->notes ?: 'Downpayment',
        ];

        if ($dp) {
            $dp->update($payload);
        } else {
            $payload['installment_plan_id'] = $plan->id;
            InstallmentPayment::create($payload);
        }
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
            'total_cost'  => (float)$request->total_cost,
            'downpayment' => (float)$request->downpayment,
            'months'      => (int)$request->months, // ✅ monthly term only
            'start_date'  => $request->start_date,
            'status'      => 'Partially Paid',
            'balance'     => 0,
        ]);

        // ✅ Downpayment stored as month 0 payment
        if ((float)$request->downpayment > 0) {
            InstallmentPayment::create([
                'installment_plan_id' => $plan->id,
                'visit_id'            => $plan->visit_id,
                'month_number'        => 0,
                'amount'              => (float)$request->downpayment,
                'method'              => 'Cash',
                'payment_date'        => $request->start_date,
                'notes'               => 'Downpayment',
            ]);
        }

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

        $this->ensureDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        return view('staff.payments.installment.show', compact('plan'));
    }

    /**
     * Optional/legacy route (manual add). Months start at 1..months.
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

        if ($plan->payments()->where('month_number', (int)$request->month_number)->exists()) {
            return back()->withErrors('This month is already paid.')->withInput();
        }

        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'month_number'        => (int)$request->month_number,
            'payment_date'        => $request->payment_date,
            'amount'              => (float)$request->amount,
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

        $this->ensureDownpaymentPayment($plan);
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

        $plan->update([
            'total_cost'  => (float)$request->total_cost,
            'downpayment' => (float)$request->downpayment,
            'months'      => (int)$request->months,
            'start_date'  => $request->start_date,
        ]);

        // ✅ Sync month 0 DP record if possible
        $this->syncDownpaymentPayment($plan);

        // ✅ keep totals correct
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
