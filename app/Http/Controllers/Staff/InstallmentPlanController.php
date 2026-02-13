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
    private function refreshPayments(InstallmentPlan $plan): void
    {
        $plan->unsetRelation('payments');
        $plan->load('payments');
    }

    private function findDownpaymentPayment(InstallmentPlan $plan): ?InstallmentPayment
    {
        $plan->loadMissing('payments');

        $down  = (float)($plan->downpayment ?? 0);
        $start = $plan->start_date ? Carbon::parse($plan->start_date)->toDateString() : null;

        return $plan->payments->first(function ($p) use ($down, $start) {
            $m     = (int)($p->month_number ?? -1);
            $notes = strtolower((string)($p->notes ?? ''));

            if ($m === 0) return true;

            if ($m === 1) {
                if (str_contains($notes, 'downpayment')) return true;

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

    private function monthShift(InstallmentPlan $plan): int
    {
        $plan->loadMissing('payments');

        $dp = $this->findDownpaymentPayment($plan);
        if (!$dp) return 0;

        $hasMonth0 = $plan->payments->contains(fn($p) => (int)($p->month_number ?? -1) === 0);
        $isLegacyMonth1 = !$hasMonth0 && (int)($dp->month_number ?? -1) === 1;

        return $isLegacyMonth1 ? 1 : 0;
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

        $this->refreshPayments($plan);
    }

    private function recomputePlan(InstallmentPlan $plan): InstallmentPlan
    {
        $this->refreshPayments($plan);

        $totalCost = (float)($plan->total_cost ?? 0);
        $down      = (float)($plan->downpayment ?? 0);

        $paymentsTotal = (float)$plan->payments->sum('amount');
        $hasDpRecord   = $this->hasDownpaymentRecord($plan);

        $paid = $paymentsTotal + ($hasDpRecord ? 0 : $down);

        $balance = max(0, $totalCost - $paid);

        $computedStatus = ($balance <= 0)
            ? InstallmentPlan::STATUS_FULLY_PAID
            : InstallmentPlan::STATUS_PARTIALLY_PAID;

        $current = strtolower(trim((string)($plan->status ?? '')));
        if ($current === strtolower(InstallmentPlan::STATUS_COMPLETED)) {
            $plan->balance = $balance;
            $plan->save();
            return $plan;
        }

        $plan->balance = $balance;
        $plan->status  = $computedStatus;
        $plan->save();

        return $plan;
    }

    private function syncDownpaymentPayment(InstallmentPlan $plan): void
    {
        $this->refreshPayments($plan);

        $down = (float)($plan->downpayment ?? 0);
        $dp   = $this->findDownpaymentPayment($plan);

        if ($down <= 0) {
            if ($dp) {
                $m     = (int)($dp->month_number ?? -1);
                $notes = strtolower((string)($dp->notes ?? ''));

                if ($m === 0 || ($m === 1 && str_contains($notes, 'downpayment'))) {
                    $dp->delete();
                    $this->refreshPayments($plan);
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
            $m = (int)($dp->month_number ?? -1);

            $payload['notes'] = ($m === 1) ? 'Downpayment' : ($dp->notes ?: 'Downpayment');
            $payload['method'] = $dp->method ?? 'Cash';

            $dp->update($payload);
            $this->refreshPayments($plan);
            return;
        }

        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'visit_id'            => $plan->visit_id,
            'month_number'        => 0,
            'amount'              => $down,
            'method'              => 'Cash',
            'payment_date'        => $plan->start_date,
            'notes'               => 'Downpayment',
        ]);

        $this->refreshPayments($plan);
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
        $isOpen = $request->boolean('is_open_contract');

        $request->validate([
            'visit_id'        => 'nullable|exists:visits,id|required_without:appointment_id',
            'appointment_id'  => 'nullable|exists:appointments,id|required_without:visit_id',
            'total_cost'      => 'required|numeric|min:0',
            'downpayment'     => 'required|numeric|min:0|lte:total_cost',
            'is_open_contract'=> 'nullable|boolean',
            'months'          => $isOpen ? 'nullable|integer|min:0' : 'required|integer|min:1',
            'open_monthly_payment' => $isOpen ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'start_date'      => 'required|date',
        ]);

        $visit = null;

        if ($request->filled('visit_id')) {
            $visit = Visit::with(['patient', 'procedures.service'])->findOrFail($request->visit_id);
        } else {
            $app = Appointment::with(['patient', 'service'])->findOrFail($request->appointment_id);

            $visit = Visit::create([
                'patient_id'   => $app->patient_id,
                'doctor_id'    => $app->doctor_id ?? null,
                'dentist_name' => $app->dentist_name ?? null,
                'visit_date'   => $request->start_date,
                'status'       => 'completed',
                'notes'        => 'Installment plan created from appointment',
                'price'        => null,
            ]);

            if (!empty($app->service_id)) {
                $visit->procedures()->create([
                    'service_id'   => $app->service_id,
                    'tooth_number' => null,
                    'surface'      => null,
                    'shade'        => null,
                    'notes'        => null,
                    'price'        => 0,
                ]);
            }

            $visit->load(['patient', 'procedures.service']);
        }

        $plan = InstallmentPlan::create([
            'visit_id'          => $visit->id,
            'patient_id'        => $visit->patient_id,
            'service_id'        => optional($visit->procedures->first()?->service)->id,
            'total_cost'        => (float)$request->total_cost,
            'downpayment'       => (float)$request->downpayment,
            'is_open_contract'  => $isOpen,
            'months'            => $isOpen ? 0 : (int)$request->months,
            // ✅ SAVE OPEN CONTRACT MONTHLY PAYMENT
            'open_monthly_payment' => $isOpen ? (float)$request->open_monthly_payment : null,
            'start_date'        => $request->start_date,
            'status'            => InstallmentPlan::STATUS_PARTIALLY_PAID,
            'balance'           => 0,
        ]);

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
        $isOpen = $request->boolean('is_open_contract');

        $request->validate([
            'total_cost'       => 'required|numeric|min:0',
            'downpayment'      => 'required|numeric|min:0|lte:total_cost',
            'is_open_contract' => 'nullable|boolean',
            'months'           => $isOpen ? 'nullable|integer|min:0' : 'required|integer|min:1',
            'open_monthly_payment' => $isOpen ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'start_date'       => 'required|date',
        ]);

        $plan->update([
            'total_cost'       => (float)$request->total_cost,
            'downpayment'      => (float)$request->downpayment,
            'is_open_contract' => $isOpen,
            'months'           => $isOpen ? 0 : (int)$request->months,
            // ✅ UPDATE OPEN CONTRACT MONTHLY PAYMENT
            'open_monthly_payment' => $isOpen ? (float)$request->open_monthly_payment : null,
            'start_date'       => $request->start_date,
        ]);

        $this->syncDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan updated successfully!');
    }

    public function restore(int $id)
    {
        $plan = InstallmentPlan::withTrashed()->findOrFail($id);

        $plan->restore();

        // keep computations consistent
        $this->syncDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        return back()->with('success', 'Installment plan restored successfully!');
    }

    public function destroy(InstallmentPlan $plan)
    {
        $label = 'Installment plan #' . $plan->id;

        $plan->delete();

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment deleted successfully')
            ->with('undo', [
                'message' => $label . ' deleted.',
                'url' => route('staff.installments.restore', $plan->id),
                'ms' => 10000,
            ]);
    }

    public function complete(Request $request, InstallmentPlan $plan)
    {
        if (!(bool)($plan->is_open_contract ?? false)) {
            return back()->with('error', 'Only Open Contract plans can be marked as completed.');
        }

        $plan->status = InstallmentPlan::STATUS_COMPLETED;
        $plan->save();

        $this->recomputePlan($plan);

        return redirect()
            ->route('staff.installments.show', $plan)
            ->with('success', 'Installment plan marked as Completed.');
    }

    public function reopen(Request $request, InstallmentPlan $plan)
    {
        if (!(bool)($plan->is_open_contract ?? false)) {
            return back()->with('error', 'Only Open Contract plans can be reopened.');
        }

        $plan->status = InstallmentPlan::STATUS_PARTIALLY_PAID;
        $plan->save();

        $this->recomputePlan($plan);

        return redirect()
            ->route('staff.installments.show', $plan)
            ->with('success', 'Installment plan reopened.');
    }
}
