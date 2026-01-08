<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Models\Visit;
use App\Models\Appointment;
use Carbon\Carbon;

class InstallmentPlanController extends Controller
{
    /**
     * Find Downpayment payment record:
     * - NEW: month_number = 0
     * - LEGACY: month_number = 1 and notes contains "downpayment"
     * - Fallback: month_number = 1 and amount ~= plan.downpayment AND date == plan.start_date
     */
    private function findDownpaymentPayment(InstallmentPlan $plan): ?InstallmentPayment
    {
        $plan->loadMissing('payments');

        $down = (float)($plan->downpayment ?? 0);
        $start = $plan->start_date ? Carbon::parse($plan->start_date)->toDateString() : null;

        return $plan->payments->first(function ($p) use ($down, $start) {
            $m = (int)($p->month_number ?? -1);
            $notes = strtolower((string)($p->notes ?? ''));

            if ($m === 0) return true;

            if ($m === 1) {
                if (str_contains($notes, 'downpayment')) return true;

                // fallback (if notes were edited/cleared)
                $amt = (float)($p->amount ?? 0);
                $pd  = $p->payment_date ? Carbon::parse($p->payment_date)->toDateString() : null;

                if ($down > 0 && abs($amt - $down) < 0.01 && $start && $pd === $start) {
                    return true;
                }
            }

            return false;
        });
    }

    private function hasDownpaymentRecord(InstallmentPlan $plan): bool
    {
        return (bool) $this->findDownpaymentPayment($plan);
    }

    /**
     * If DP is legacy month 1 (and there is no month 0 DP), views/controllers may "shift" months.
     * This is mainly for UI logic, but we keep the helper here for consistency.
     */
    private function monthShift(InstallmentPlan $plan): int
    {
        $plan->loadMissing('payments');

        $dp = $this->findDownpaymentPayment($plan);
        if (!$dp) return 0;

        $hasMonth0 = $plan->payments->contains(fn($p) => (int)($p->month_number ?? -1) === 0);
        $isLegacyMonth1 = !$hasMonth0 && (int)($dp->month_number ?? -1) === 1;

        return $isLegacyMonth1 ? 1 : 0;
    }

    /**
     * Ensure downpayment payment row exists as month_number=0 (NEW format).
     * - If legacy DP exists (month 1 "downpayment"), DO NOT create month 0 (avoid double DP).
     */
    private function ensureDownpaymentPayment(InstallmentPlan $plan): void
    {
        $plan->loadMissing('payments');

        $down = (float)($plan->downpayment ?? 0);
        if ($down <= 0) return;

        // If any DP already exists (month 0 or legacy month 1), do nothing
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
     * Keep DP payment synced when plan is edited:
     * - If month 0 DP exists -> update it
     * - Else if legacy month 1 DP exists -> update it (and keep/restore notes to "Downpayment")
     * - Else -> create month 0 DP if downpayment > 0
     */
    private function syncDownpaymentPayment(InstallmentPlan $plan): void
    {
        $plan->loadMissing('payments');

        $down = (float)($plan->downpayment ?? 0);
        $dp = $this->findDownpaymentPayment($plan);

        // If downpayment becomes 0, delete month 0 DP safely.
        // For legacy month 1 DP, we delete ONLY if notes clearly say downpayment.
        if ($down <= 0) {
            if ($dp) {
                $m = (int)($dp->month_number ?? -1);
                $notes = strtolower((string)($dp->notes ?? ''));

                if ($m === 0 || ($m === 1 && str_contains($notes, 'downpayment'))) {
                    $dp->delete();
                }
            }
            return;
        }

        $payload = [
            'visit_id'     => $plan->visit_id,
            'amount'       => $down,
            'payment_date' => $plan->start_date,
        ];

        if ($dp) {
            // If legacy month 1 dp, enforce a stable note so DP detection stays correct
            $m = (int)($dp->month_number ?? -1);
            if ($m === 1) {
                $payload['notes'] = 'Downpayment';
            } else {
                $payload['notes'] = $dp->notes ?: 'Downpayment';
            }

            $payload['method'] = $dp->method ?? 'Cash';

            $dp->update($payload);
            return;
        }

        // No DP payment found, create new month 0 DP
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
            'months'      => (int)$request->months, // monthly term only
            'start_date'  => $request->start_date,
            'status'      => 'Partially Paid',
            'balance'     => 0,
        ]);

        // ✅ For NEW plans: DP is month 0 (never month 1)
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

        $this->syncDownpaymentPayment($plan);
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
