<?php

namespace App\Services;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PaymentTransactionService
{
    public function __construct(private FinancialService $finance) {}

    public function paginate(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $ordinary = DB::table('payments as tx')
            ->join('visits as v', 'v.id', '=', 'tx.visit_id')
            ->join('patients as patient', 'patient.id', '=', 'v.patient_id')
            ->leftJoin('visit_procedures as vp', 'vp.id', '=', 'tx.visit_procedure_id')
            ->leftJoin('services as service', 'service.id', '=', 'vp.service_id')
            ->whereNull('tx.deleted_at')->whereNull('v.deleted_at')->whereNull('patient.deleted_at')
            ->selectRaw("'ordinary' as source, tx.id as source_id, tx.payment_date, tx.amount, tx.method, tx.notes, patient.id as patient_id, patient.first_name, patient.last_name, v.id as target_id, COALESCE(service.name, (SELECT s2.name FROM visit_procedures vp2 JOIN services s2 ON s2.id = vp2.service_id WHERE vp2.visit_id = v.id LIMIT 1), 'Visit payment') as treatment, v.status as target_status, 1 as type_order");

        $plans = DB::table('installment_payments as tx')
            ->join('installment_plans as plan', 'plan.id', '=', 'tx.installment_plan_id')
            ->join('patients as patient', 'patient.id', '=', 'plan.patient_id')
            ->leftJoin('services as service', 'service.id', '=', 'plan.service_id')
            ->whereNull('tx.deleted_at')->whereNull('plan.deleted_at')->whereNull('patient.deleted_at')
            ->selectRaw("'installment' as source, tx.id as source_id, tx.payment_date, tx.amount, tx.method, tx.notes, patient.id as patient_id, patient.first_name, patient.last_name, plan.id as target_id, COALESCE(service.name, 'Installment plan') as treatment, plan.status as target_status, 2 as type_order");

        foreach ([$ordinary, $plans] as $query) {
            if ($request->filled('patient_id')) $query->where('patient.id', $request->integer('patient_id'));
            if ($request->filled('method')) $query->where('tx.method', $request->method);
            if ($request->filled('date_from')) $query->whereDate('tx.payment_date', '>=', $request->date_from);
            if ($request->filled('date_to')) $query->whereDate('tx.payment_date', '<=', $request->date_to);
            if ($request->filled('q')) {
                $term = '%' . trim($request->q) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('patient.first_name', 'like', $term)->orWhere('patient.last_name', 'like', $term)
                        ->orWhere('service.name', 'like', $term)->orWhere('tx.notes', 'like', $term)
                        ->orWhere('tx.id', 'like', $term);
                });
            }
        }
        if ($request->filled('status')) {
            $ordinary->where('v.status', $request->status);
            $plans->where('plan.status', $request->status);
        }
        if ($request->type === 'ordinary') $plans->whereRaw('1 = 0');
        if (in_array($request->type, ['downpayment', 'installment'], true)) $ordinary->whereRaw('1 = 0');
        if ($request->type === 'downpayment') $plans->where(function ($q) {
            $q->where('tx.month_number', 0)->orWhereRaw("LOWER(COALESCE(tx.notes, '')) LIKE '%downpayment%'");
        });
        if ($request->type === 'installment') $plans->where('tx.month_number', '>', 0)
            ->whereRaw("LOWER(COALESCE(tx.notes, '')) NOT LIKE '%downpayment%'");

        $query = DB::query()->fromSub($ordinary->unionAll($plans), 'ledger');
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'oldest' => $query->orderBy('payment_date')->orderBy('source_id'),
            'amount_high' => $query->orderByDesc('amount')->orderByDesc('payment_date'),
            'amount_low' => $query->orderBy('amount')->orderByDesc('payment_date'),
            'patient' => $query->orderBy('last_name')->orderBy('first_name')->orderByDesc('payment_date'),
            default => $query->orderByDesc('payment_date')->orderByDesc('source_id'),
        };
        $page = $query->paginate($perPage)->withQueryString();

        $ordinaryModels = Payment::with(['visit.procedures.service', 'visit.payments', 'procedure.service'])
            ->whereIn('id', collect($page->items())->where('source', 'ordinary')->pluck('source_id'))->get()->keyBy('id');
        $planModels = InstallmentPayment::with(['plan.payments', 'plan.service'])
            ->whereIn('id', collect($page->items())->where('source', 'installment')->pluck('source_id'))->get()->keyBy('id');

        $page->setCollection(collect($page->items())->map(function ($row) use ($ordinaryModels, $planModels) {
            $row->patient_name = trim($row->last_name . ', ' . $row->first_name);
            if ($row->source === 'ordinary') {
                $model = $ordinaryModels->get($row->source_id);
                $row->reference = 'PAY-' . str_pad($row->source_id, 5, '0', STR_PAD_LEFT);
                $row->type = 'Visit payment';
                $row->balance_after = $model ? $this->ordinaryBalanceAfter($model) : 0;
                $row->show_url = route('staff.payments.show', $row->source_id);
                $row->edit_url = route('staff.payments.edit', $row->source_id);
            } else {
                $model = $planModels->get($row->source_id);
                $dp = $model ? $this->finance->downpaymentPayment($model->plan) : null;
                $row->reference = 'INS-' . str_pad($row->source_id, 5, '0', STR_PAD_LEFT);
                $row->type = $dp && $dp->is($model) ? 'Downpayment' : 'Installment';
                $row->balance_after = $model ? $this->planBalanceAfter($model) : 0;
                $row->show_url = $model ? route('staff.installments.show', $model->installment_plan_id) : '#';
                $row->edit_url = $model ? route('staff.installments.payments.edit', [$model->installment_plan_id, $model->id]) : '#';
            }
            return $row;
        }));
        return $page;
    }

    private function ordinaryBalanceAfter(Payment $payment): float
    {
        $charge = $this->finance->visitCharge($payment->visit);
        $paid = Payment::where('visit_id', $payment->visit_id)
            ->where(fn ($q) => $q->whereDate('payment_date', '<', $payment->payment_date)
                ->orWhere(fn ($same) => $same->whereDate('payment_date', $payment->payment_date)->where('id', '<=', $payment->id)))
            ->sum('amount');
        return max(0, $charge - (float) $paid);
    }

    private function planBalanceAfter(InstallmentPayment $payment): float
    {
        $plan = $payment->plan;
        $paid = InstallmentPayment::where('installment_plan_id', $plan->id)
            ->where(fn ($q) => $q->whereDate('payment_date', '<', $payment->payment_date)
                ->orWhere(fn ($same) => $same->whereDate('payment_date', $payment->payment_date)->where('id', '<=', $payment->id)))
            ->sum('amount');
        if (!$this->finance->downpaymentPayment($plan)) $paid += (float) $plan->downpayment;
        return max(0, (float) $plan->total_cost - (float) $paid);
    }
}
