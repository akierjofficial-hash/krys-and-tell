<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\InstallmentPlanCreationService;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Models\Visit;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Services\AdminAuditService;
use App\Services\OpenMonthlyContractService;
use App\Services\FinancialService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        if ($plan->hasUnknownTotal()) return;
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
        if ($plan->hasUnknownTotal()) return app(FinancialService::class)->recomputePlan($plan);
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
        if ($plan->hasUnknownTotal()) return;
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
        return redirect()->route('staff.payments.index', [
            'tab' => 'plans',
            'q' => $request->query('search', $request->query('q', '')),
        ]);
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

    public function store(Request $request, InstallmentPlanCreationService $creator)
    {
        $unknown = $request->boolean('is_unpriced_contract');
        $isOpen = $unknown || $request->boolean('is_open_contract');
        $data = $request->validate([
            'visit_id' => 'nullable|exists:visits,id|required_without:appointment_id',
            'appointment_id' => 'nullable|exists:appointments,id|required_without:visit_id',
            'total_cost' => $unknown ? 'nullable' : 'required|numeric|min:0',
            'downpayment' => $unknown ? 'required|numeric|gt:0' : 'required|numeric|min:0|lte:total_cost',
            'is_unpriced_contract' => 'nullable|boolean',
            'first_due_date' => $unknown ? 'required|date|after_or_equal:start_date' : 'nullable|date',
            'is_open_contract' => 'nullable|boolean',
            'months' => $isOpen ? 'nullable|integer|min:0' : 'required|integer|min:1',
            'open_monthly_payment' => $unknown ? 'required|numeric|gt:0' : ($isOpen ? 'required|numeric|min:0' : 'nullable|numeric|min:0'),
            'start_date' => 'required|date',
            'downpayment_method' => 'required|in:Cash,GCash,Card,Bank Transfer',
            'downpayment_date' => 'required|date',
            'submission_token' => 'required|uuid',
        ]);
        if ($request->filled('visit_id') && $request->filled('appointment_id')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['visit_id' => 'Select only one source.']);
        }
        $data['is_open_contract'] = $isOpen;
        $data['is_unpriced_contract'] = $unknown;
        $creator->create($data);
        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'plans'])
            ->with('success', 'Installment plan created.');
    }
    public function show(InstallmentPlan $plan, OpenMonthlyContractService $monthly)
    {
        $plan->load([
            'patient',
            'service',
            'visit.patient',
            'visit.procedures.service',
            'payments',
        ]);

        $openContractDetails = $plan->is_unpriced_contract ? $monthly->details($plan) : null;
        return view('staff.payments.installment.show', compact('plan', 'openContractDetails'));
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

        return view('staff.payments.installment.edit', compact('plan', 'visits', 'appointments'));
    }

    public function update(Request $request, InstallmentPlan $plan)
    {
        if ($plan->is_unpriced_contract) {
            throw ValidationException::withMessages(['total_cost' => 'Use Agree final total on the plan page; changes to an unknown-total contract must be audited.']);
        }
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

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan updated successfully!');
    }

    public function restore(Request $request, int $id)
    {
        $plan = InstallmentPlan::withTrashed()->findOrFail($id);

        $plan->restore();

        // keep computations consistent
        $this->syncDownpaymentPayment($plan);
        $this->recomputePlan($plan);

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan restored successfully!');
    }

    public function destroy(Request $request, InstallmentPlan $plan)
    {
        $label = 'Installment plan #' . $plan->id;

        $plan->delete();

        $returnUrl = $this->ktReturnUrl($request, 'staff.payments.index', ['tab' => 'installment']);

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment deleted successfully')
            ->with('undo', [
                'message' => $label . ' deleted.',
                'url' => route('staff.installments.restore', ['id' => $plan->id, 'return' => $returnUrl]),
                'ms' => 10000,
            ]);
    }

    public function complete(Request $request, InstallmentPlan $plan, OpenMonthlyContractService $monthly, AdminAuditService $audit)
    {
        if ($plan->is_unpriced_contract && $plan->status === InstallmentPlan::STATUS_COMPLETED) {
            throw ValidationException::withMessages(['ended_at' => 'This contract is already closed.']);
        }
        if (!(bool)($plan->is_open_contract ?? false)) {
            return back()->with('error', 'Only Open Contract plans can be marked as completed.');
        }

        if ($plan->is_unpriced_contract) {
            $data = $request->validate(['ended_at' => ['required', 'date', 'after_or_equal:'.$plan->start_date?->toDateString(), 'before_or_equal:today'],
                'acknowledge_unpaid' => ['nullable', 'accepted'], 'closure_note' => ['required', 'string', 'min:5', 'max:1000']]);
            $due = $monthly->details($plan, $data['ended_at'])['unpaid_due'];
            if ($due > 0 && !$request->boolean('acknowledge_unpaid')) {
                throw ValidationException::withMessages(['acknowledge_unpaid' => 'Unpaid monthly obligations at the selected end date are ₱'.number_format($due, 2).'. Review and acknowledge this amount before closing.']);
            }
            DB::transaction(function () use ($plan, $data, $audit, $due, $request) {
                $plan->update(['status' => InstallmentPlan::STATUS_COMPLETED, 'ended_at' => $data['ended_at']]);
                $audit->record($request->user(), 'open_contract_closed', $plan,
                    'Closed unknown-total monthly contract', [], ['ended_at' => $data['ended_at'], 'unpaid_due' => $due],
                    $data['closure_note'], true);
            });
        } else {
            $plan->status = InstallmentPlan::STATUS_COMPLETED;
            $plan->save();
        }

        $this->recomputePlan($plan);

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan marked as Completed.');
    }

    public function reopen(Request $request, InstallmentPlan $plan, AdminAuditService $audit)
    {
        if (!(bool)($plan->is_open_contract ?? false)) {
            return back()->with('error', 'Only Open Contract plans can be reopened.');
        }

        if ($plan->is_unpriced_contract && $plan->status !== InstallmentPlan::STATUS_COMPLETED) {
            throw ValidationException::withMessages(['plan' => 'Only a closed plan can be reopened.']);
        }
        DB::transaction(function () use ($plan, $request, $audit) {
            $previousEnd = $plan->ended_at?->toDateString();
            $plan->status = InstallmentPlan::STATUS_PARTIALLY_PAID;
            if ($plan->is_unpriced_contract) $plan->ended_at = null;
            $plan->save();
            if ($plan->is_unpriced_contract) $audit->record($request->user(), 'open_contract_reopened', $plan,
                'Reopened monthly contract', ['ended_at' => $previousEnd], ['ended_at' => null], null, true);
        });

        $this->recomputePlan($plan);

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan reopened.');
    }

    public function agreeTotal(Request $request, InstallmentPlan $plan, FinancialService $finance, AdminAuditService $audit)
    {
        abort_unless($plan->hasUnknownTotal(), 404);
        $data = $request->validate(['total_cost' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'reason' => ['required', 'string', 'min:5', 'max:1000']]);
        DB::transaction(function () use ($request, $plan, $data, $finance, $audit) {
            $plan = InstallmentPlan::with('payments')->lockForUpdate()->findOrFail($plan->id);
            if (!$plan->hasUnknownTotal()) throw ValidationException::withMessages(['total_cost' => 'A final total was already recorded.']);
            if ((float) $data['total_cost'] < (float) $plan->payments->sum('amount')) {
                throw ValidationException::withMessages(['total_cost' => 'The final total cannot be less than recorded receipts.']);
            }
            $plan->update(['total_cost' => $data['total_cost'], 'total_agreed_at' => now(), 'total_agreed_by' => $request->user()->id]);
            $finance->recomputePlan($plan);
            $audit->record($request->user(), 'open_contract_total_agreed', $plan,
                'Recorded a later agreed final total', ['total_cost' => null], ['total_cost' => $data['total_cost']], $data['reason'], true);
        });
        return redirect()->route('staff.installments.show', $plan)->with('success', 'Agreed final total recorded with an audit trail.');
    }
}
