<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\InstallmentPlan;
use App\Models\Visit;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\FinancialService;
use App\Services\PaymentTransactionService;
use App\Services\PaymentWorkflowService;
use App\Services\InstallmentPlanCreationService;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // =======================
    // Helpers
    // =======================
    private function visitDue(Visit $visit): float
    {
        if ($visit->price !== null) {
            return (float) $visit->price;
        }

        if ($visit->relationLoaded('procedures')) {
            return (float) $visit->procedures->sum(fn ($p) => (float) ($p->price ?? 0));
        }

        return (float) $visit->procedures()->sum('price');
    }

    private function maybeApplyCustomTotalOverride(Visit $visit, float $newTotalDue): bool
    {
        $currentDue = $this->visitDue($visit);
        if ($newTotalDue >= $currentDue) return false;

        $visit->loadMissing('procedures.service');

        $hasCustom = $visit->procedures->contains(fn ($p) => (bool) ($p->service?->allow_custom_price ?? false));
        if (!$hasCustom) return false;

        $fixedMin = (float) $visit->procedures
            ->filter(fn ($p) => !((bool) ($p->service?->allow_custom_price ?? false)))
            ->sum(fn ($p) => (float) ($p->price ?? 0));

        if ($newTotalDue < $fixedMin) return false;

        $visit->price = $newTotalDue;
        $visit->save();

        return true;
    }

    public function cashPatient(Patient $patient)
    {
        $payments = Payment::with(['visit.procedures.service', 'visit.patient', 'procedure.service'])
            ->whereIn('method', ['Cash', 'GCash', 'Card', 'Bank Transfer'])
            ->whereHas('visit', fn($q) => $q->where('patient_id', $patient->id))
            ->orderByDesc('payment_date')
            ->limit(200)
            ->get();

        $html = view('staff.payments._cash_patient_details', compact('payments', 'patient'))->render();

        return response()->json(['html' => $html]);
    }

    private function visitPaid(Visit $visit): float
    {
        if (isset($visit->total_paid)) {
            return (float) ($visit->total_paid ?? 0);
        }

        if (isset($visit->paid_total)) {
            return (float) ($visit->paid_total ?? 0);
        }

        if ($visit->relationLoaded('payments')) {
            return (float) $visit->payments->sum(fn ($p) => (float) ($p->amount ?? 0));
        }

        return (float) Payment::where('visit_id', $visit->id)->sum('amount');
    }

    private function visitBalance(Visit $visit): float
    {
        return $this->visitDue($visit) - $this->visitPaid($visit);
    }

    private function hasInstallmentPlanForVisit(int $visitId): bool
    {
        return InstallmentPlan::where('visit_id', $visitId)->exists();
    }

    private function updateVisitStatusBasedOnPayments(Visit $visit): void
    {
        if ($this->hasInstallmentPlanForVisit($visit->id)) {
            $visit->update(['status' => 'installment']);

            return;
        }

        $due = $this->visitDue($visit);
        $paid = $this->visitPaid($visit);

        if ($due > 0 && $paid >= $due) {
            $visit->update(['status' => 'completed']);
        } else {
            $visit->update(['status' => 'partial']);
        }
    }

    // =======================
    // MAIN PAGE
    // =======================
    public function index(Request $request, PaymentTransactionService $ledger, FinancialService $finance)
    {
        $tab = in_array($request->tab, ['plans', 'installment'], true) ? 'plans' : 'transactions';
        $transactions = $ledger->paginate($request);
        $plansQuery = InstallmentPlan::with(['patient', 'service', 'payments']);
        if ($request->filled('q')) {
            $term = trim($request->q);
            $plansQuery->where(function ($query) use ($term) {
                $query->whereHas('patient', fn ($q) => $q
                    ->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%"))
                    ->orWhereHas('service', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                $parts = preg_split('/[\s,]+/u', $term, -1, PREG_SPLIT_NO_EMPTY);
                if (count($parts) > 1) {
                    $query->orWhereHas('patient', function ($name) use ($parts) {
                        foreach ($parts as $part) {
                            $name->where(fn ($piece) => $piece->where('first_name', 'like', "%{$part}%")
                                ->orWhere('last_name', 'like', "%{$part}%")
                                ->orWhere('middle_name', 'like', "%{$part}%"));
                        }
                    });
                }
                if (ctype_digit($term)) $query->orWhere('id', (int) $term);
            });
        }
        if ($request->filled('patient_id')) $plansQuery->where('patient_id', $request->integer('patient_id'));
        if ($request->filled('status')) $plansQuery->where('status', $request->status);
        if ($request->filled('date_from')) $plansQuery->whereDate('start_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $plansQuery->whereDate('start_date', '<=', $request->date_to);
        match ($request->input('sort', 'newest')) {
            'oldest' => $plansQuery->orderBy('start_date')->orderBy('id'),
            'start_newest' => $plansQuery->orderByDesc('start_date')->orderByDesc('id'),
            'patient' => $plansQuery->orderBy('patient_id')->orderByDesc('start_date'),
            default => $plansQuery->orderByDesc('created_at')->orderByDesc('id'),
        };
        $plans = $plansQuery->paginate(15, ['*'], 'plans_page')->withQueryString();
        $plans->getCollection()->each(function ($plan) use ($finance) {
            $plan->computed_paid = $finance->planPaid($plan);
            $plan->computed_balance = $finance->planBalance($plan);
        });
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $summary = $finance->summary();
        $submissionToken = (string) Str::uuid();
        return view('staff.payments.index', compact('tab', 'transactions', 'plans', 'patients', 'summary', 'submissionToken'));
    }

    public function payableItems(Request $request, FinancialService $finance)
    {
        $validated = $request->validate(['patient_id' => 'required|exists:patients,id']);
        $patientId = (int) $validated['patient_id'];
        $visits = Visit::with(['procedures.service', 'payments'])
            ->where('patient_id', $patientId)->whereDoesntHave('installmentPlan')->orderByDesc('visit_date')->get()
            ->map(function ($visit) use ($finance) {
                $balance = $finance->visitBalance($visit);
                $services = $visit->procedures->pluck('service.name')->filter()->unique()->join(', ');
                return ['type' => 'visit', 'id' => $visit->id, 'balance' => $balance,
                    'label' => 'Visit #' . $visit->id . ' · ' . optional($visit->visit_date)->format('M j, Y') . ' · ' . ($services ?: 'Treatment')];
            })->filter(fn ($item) => $item['balance'] > 0)->values();
        $plans = InstallmentPlan::with(['service', 'payments'])->where('patient_id', $patientId)
            ->where('status', '!=', InstallmentPlan::STATUS_COMPLETED)->orderByDesc('start_date')->get()
            ->map(fn ($plan) => ['type' => 'plan', 'id' => $plan->id, 'balance' => $finance->planBalance($plan),
                'label' => 'Plan #' . $plan->id . ' · ' . ($plan->service?->name ?: 'Treatment plan')])
            ->filter(fn ($item) => $item['balance'] > 0)->values();
        return response()->json(['items' => $visits->concat($plans)->values()]);
    }

    public function record(Request $request, PaymentWorkflowService $workflow)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'target_type' => 'required|in:visit,plan',
            'target_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|gt:0',
            'method' => 'required|in:Cash,GCash,Card,Bank Transfer',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
            'submission_token' => 'required|uuid',
            'return' => 'nullable|string',
        ]);
        $workflow->record($validated);
        return $this->ktRedirectToReturn($request, 'staff.payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function choosePlan()
    {
        return view('staff.payments.choose-plan');
    }

    // =======================
    // CASH BASIS
    // =======================
    public function createCash(Request $request)
    {
        $request->validate(['patient_id' => 'nullable|exists:patients,id']);
        $installmentVisitIds = InstallmentPlan::whereNotNull('visit_id')->pluck('visit_id')->all();
        $installmentSet = array_flip($installmentVisitIds);

        $visits = Visit::with(['patient', 'procedures.service'])
            ->whereHas('procedures')
            ->withSum('payments as total_paid', 'amount')
            ->orderByDesc('visit_date')
            ->get()
            ->filter(function (Visit $visit) use ($installmentSet) {
                if (isset($installmentSet[$visit->id])) return false;

                $due = $this->visitDue($visit);
                $paid = (float) ($visit->total_paid ?? 0);
                $balance = $due - $paid;

                return $due > 0 && $balance > 0;
            })
            ->values();

        $payableStatuses = ['scheduled', 'upcoming', 'approved', 'confirmed'];

        $appointments = Appointment::with(['patient', 'service'])
            ->whereNotIn('status', ['completed', 'cancelled', 'declined'])
            ->whereIn('status', $payableStatuses)
            ->whereNotNull('patient_id')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get();

        if ($request->filled('patient_id')) {
            $visits = $visits->where('patient_id', $request->patient_id)->values();
            $appointments = $appointments->where('patient_id', $request->patient_id)->values();
        }
        return view('staff.payments.create_cash', compact('visits', 'appointments'));
    }

    public function storeCash(Request $request)
    {
        $request->validate([
            'method'         => 'required',
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|gt:0',
            'visit_id'       => 'nullable|exists:visits,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'preserve_charge' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (!$request->visit_id && !$request->appointment_id) {
            return back()->withErrors('Please select a Visit or an Appointment.')->withInput();
        }

        if ($request->visit_id && $request->appointment_id) {
            return back()->withErrors('Please select only one source.')->withInput();
        }

        $amount = (float) $request->amount;
        $epsilon = 0.0001;

        if ($request->visit_id) {
            DB::transaction(function () use ($request, $amount, $epsilon): void {
                $visit = Visit::query()
                    ->with(['procedures.service', 'payments'])
                    ->lockForUpdate()
                    ->findOrFail((int) $request->visit_id);

                if ($visit->procedures->isEmpty()) {
                    throw ValidationException::withMessages([
                        'visit_id' => 'This visit has no procedures to charge.',
                    ]);
                }

                if ($this->hasInstallmentPlanForVisit($visit->id)) {
                    throw ValidationException::withMessages([
                        'visit_id' => 'This visit is under an installment plan. Please collect payment from the plan instead.',
                    ]);
                }

                $due = $this->visitDue($visit);
                $paid = $this->visitPaid($visit);
                $balance = $due - $paid;

                if ($due <= 0 || $balance <= 0) {
                    throw ValidationException::withMessages([
                        'visit_id' => 'This visit is already fully paid.',
                    ]);
                }

                if ($amount > $balance + $epsilon) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount cannot be greater than the remaining balance.',
                    ]);
                }

                if (!$request->boolean('preserve_charge') && $amount + $epsilon < $balance) {
                    $desiredFinalTotal = $paid + $amount;
                    $this->maybeApplyCustomTotalOverride($visit, $desiredFinalTotal);

                    $visit->refresh();
                    $visit->load(['procedures.service', 'payments']);

                    $remainingAfterOverride = $this->visitBalance($visit);

                    if ($amount > $remainingAfterOverride + $epsilon) {
                        throw ValidationException::withMessages([
                            'amount' => 'Payment amount cannot be greater than the remaining balance.',
                        ]);
                    }
                }

                Payment::create([
                    'visit_id'     => $visit->id,
                    'amount'       => $amount,
                    'method'       => $request->method,
                    'payment_date' => $request->payment_date,
                    'notes'        => $request->notes,
                ]);

                $visit->load('payments');
                $this->updateVisitStatusBasedOnPayments($visit);
            });

            return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'cash'])
                ->with('success', 'Payment recorded.');
        }

        if ($request->appointment_id) {
            $payableStatuses = ['scheduled', 'upcoming', 'approved', 'confirmed'];

            DB::transaction(function () use ($request, $amount, $epsilon, $payableStatuses): void {
                $appointment = Appointment::query()
                    ->with(['patient', 'service'])
                    ->lockForUpdate()
                    ->findOrFail((int) $request->appointment_id);

                if (!$appointment->patient_id) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'This appointment has no patient record yet. Approve it first.',
                    ]);
                }

                $status = strtolower((string) $appointment->status);
                if (in_array($status, ['completed', 'cancelled', 'declined'], true)) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'This appointment is not payable (already completed/cancelled/declined).',
                    ]);
                }

                if ($status !== '' && !in_array($status, $payableStatuses, true)) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'This appointment status is not payable.',
                    ]);
                }

                $visit = Visit::create([
                    'patient_id' => $appointment->patient_id,
                    'source_appointment_id' => $appointment->id,
                    'visit_date' => now()->toDateString(),
                    'status'     => 'partial',
                ]);

                if ($appointment->service_id) {
                    $visit->procedures()->create([
                        'service_id'   => $appointment->service_id,
                        'tooth_number' => null,
                        'surface'      => null,
                        'shade'        => null,
                        'notes'        => 'From appointment',
                        'price'        => $appointment->service->base_price ?? 0,
                    ]);
                }

                $visit->load(['procedures.service', 'payments']);
                $due = $this->visitDue($visit);
                $paid = $this->visitPaid($visit);
                $balance = $due - $paid;

                if ($due <= 0 || $balance <= 0) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'This appointment has no payable amount.',
                    ]);
                }

                if ($amount > $balance + $epsilon) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount cannot be greater than the remaining balance.',
                    ]);
                }

                if (!$request->boolean('preserve_charge') && $amount + $epsilon < $balance) {
                    $desiredFinalTotal = $paid + $amount;
                    $this->maybeApplyCustomTotalOverride($visit, $desiredFinalTotal);

                    $visit->refresh();
                    $visit->load(['procedures.service', 'payments']);

                    $remainingAfterOverride = $this->visitBalance($visit);

                    if ($amount > $remainingAfterOverride + $epsilon) {
                        throw ValidationException::withMessages([
                            'amount' => 'Payment amount cannot be greater than the remaining balance.',
                        ]);
                    }
                }

                Payment::create([
                    'visit_id'     => $visit->id,
                    'amount'       => $amount,
                    'method'       => $request->method,
                    'payment_date' => $request->payment_date,
                    'notes'        => $request->notes,
                ]);

                $visit->load(['procedures', 'payments']);
                $this->updateVisitStatusBasedOnPayments($visit);

                $appointment->update([
                    'status' => $this->visitBalance($visit) <= $epsilon ? 'completed' : 'done',
                ]);
            });

            return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'cash'])
                ->with('success', 'Payment recorded and appointment updated!');
        }

        return back()->withErrors('Something went wrong. Please try again.')->withInput();
    }

    // =======================
    // INSTALLMENT BASIS (CREATE FORM)
    // =======================
    public function createInstallment(Request $request)
    {
        $request->validate(['patient_id' => 'nullable|exists:patients,id']);
        $installmentVisitIds = InstallmentPlan::whereNotNull('visit_id')->pluck('visit_id')->all();
        $installmentSet = array_flip($installmentVisitIds);

        $visits = Visit::with(['patient', 'procedures.service'])
            ->whereHas('procedures')
            ->withSum('payments as total_paid', 'amount')
            ->orderByDesc('visit_date')
            ->get()
            ->filter(function (Visit $visit) use ($installmentSet) {
                if (isset($installmentSet[$visit->id])) return false;

                $paid = (float) ($visit->total_paid ?? 0);
                if ($paid > 0) return false;

                $due = $this->visitDue($visit);
                return $due > 0;
            })
            ->values();

        $payableStatuses = ['scheduled', 'upcoming', 'approved', 'confirmed'];

        $appointments = Appointment::with(['patient', 'service'])
            ->whereNotIn('status', ['completed', 'cancelled', 'declined'])
            ->whereIn('status', $payableStatuses)
            ->whereNotNull('patient_id')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get();

        if ($request->filled('patient_id')) {
            $visits = $visits->where('patient_id', $request->patient_id)->values();
            $appointments = $appointments->where('patient_id', $request->patient_id)->values();
        }
        return view('staff.payments.installment.create', compact('visits', 'appointments'));
    }

    public function storeInstallment(Request $request, InstallmentPlanCreationService $creator)
    {
        $isOpen = $request->boolean('is_open_contract');
        $data = $request->validate([
            'visit_id' => 'nullable|exists:visits,id|required_without:appointment_id',
            'appointment_id' => 'nullable|exists:appointments,id|required_without:visit_id',
            'total_cost' => 'required|numeric|min:0',
            'downpayment' => 'required|numeric|min:0|lte:total_cost',
            'is_open_contract' => 'nullable|boolean',
            'open_monthly_payment' => $isOpen ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'months' => $isOpen ? 'nullable|integer|min:0' : 'required|integer|min:1',
            'start_date' => 'required|date',
            'downpayment_method' => 'required|in:Cash,GCash,Card,Bank Transfer',
            'downpayment_date' => 'required|date',
            'submission_token' => 'required|uuid',
        ]);
        if ($request->filled('visit_id') && $request->filled('appointment_id')) {
            throw ValidationException::withMessages(['visit_id' => 'Select only one source.']);
        }
        $data['is_open_contract'] = $isOpen;
        $creator->create($data);
        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'plans'])
            ->with('success', 'Installment plan created.');
    }
    // CASH EDIT / DELETE / SHOW
    // =======================
    public function edit(Payment $payment)
    {
        $payment->loadMissing('visit.patient', 'visit.procedures.service', 'procedure.service');

        $visits = Visit::with(['patient', 'procedures.service'])
            ->orderByDesc('visit_date')
            ->get();

        return view('staff.payments.edit', compact('payment', 'visits'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'visit_id'     => 'required|exists:visits,id',
            'amount'       => 'required|numeric|gt:0',
            'method'       => 'required',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:2000',
        ]);

        $oldVisitId = (int) $payment->visit_id;
        $newVisitId = (int) $request->visit_id;
        $newAmount = (float) $request->amount;
        $epsilon = 0.0001;

        DB::transaction(function () use ($payment, $oldVisitId, $newVisitId, $newAmount, $request, $epsilon): void {
            $newVisit = Visit::query()
                ->with(['procedures.service'])
                ->lockForUpdate()
                ->findOrFail($newVisitId);

            $allocatedProcedure = $oldVisitId === $newVisitId ? $payment->procedure : null;
            $due = $allocatedProcedure ? (float) $allocatedProcedure->price : $this->visitDue($newVisit);
            $paidWithoutCurrentQuery = Payment::query()
                ->where('visit_id', $newVisitId)
                ->where('id', '!=', $payment->id);
            if ($allocatedProcedure) {
                $paidWithoutCurrentQuery->where('visit_procedure_id', $allocatedProcedure->id);
            }
            $paidWithoutCurrent = (float) $paidWithoutCurrentQuery->sum('amount');
            $remaining = $due - $paidWithoutCurrent;

            if ($due <= 0 || $remaining <= 0) {
                throw ValidationException::withMessages([
                    'visit_id' => $allocatedProcedure
                        ? 'The treatment assigned to this receipt has no payable balance.'
                        : 'Selected visit has no payable balance.',
                ]);
            }

            if ($newAmount > $remaining + $epsilon) {
                throw ValidationException::withMessages([
                    'amount' => 'Updated amount cannot be greater than the remaining balance.',
                ]);
            }

            $attributes = $request->only('visit_id', 'amount', 'method', 'payment_date', 'notes');
            if ($oldVisitId !== $newVisitId) {
                $attributes['visit_procedure_id'] = null;
            }
            $payment->update($attributes);

            $reloadedNewVisit = Visit::with(['procedures.service', 'payments'])->find($newVisitId);
            if ($reloadedNewVisit) {
                $this->updateVisitStatusBasedOnPayments($reloadedNewVisit);
            }

            if ($oldVisitId > 0 && $oldVisitId !== $newVisitId) {
                $oldVisit = Visit::with(['procedures.service', 'payments'])->find($oldVisitId);
                if ($oldVisit) {
                    $this->updateVisitStatusBasedOnPayments($oldVisit);
                }
            }
        });

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'cash'])
            ->with('success', 'Payment updated!');
    }

    public function restore(Request $request, int $id)
    {
        $payment = Payment::withTrashed()->findOrFail($id);
        $visitId = $payment->visit_id;

        $payment->restore();

        if ($visitId) {
            $visit = Visit::with(['procedures', 'payments'])->find($visitId);
            if ($visit) {
                $this->updateVisitStatusBasedOnPayments($visit);
            }
        }

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'cash'])
            ->with('success', 'Payment restored successfully!');
    }

    public function destroy(Request $request, Payment $payment)
    {
        $visitId = $payment->visit_id;
        $label = 'Payment #' . $payment->id;
        if (!is_null($payment->amount)) {
            $label .= ' (₱' . number_format((float)$payment->amount, 2) . ')';
        }

        $payment->delete();

        if ($visitId) {
            $visit = Visit::with(['procedures', 'payments'])->find($visitId);
            if ($visit) {
                $this->updateVisitStatusBasedOnPayments($visit);
            }
        }

        $returnUrl = $this->ktReturnUrl($request, 'staff.payments.index', ['tab' => 'cash']);

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'cash'])
            ->with('success', 'Payment removed!')
            ->with('undo', [
                'message' => $label . ' deleted.',
                'url' => route('staff.payments.restore', ['id' => $payment->id, 'return' => $returnUrl]),
                'ms' => 10000,
            ]);
    }

    public function show(Payment $payment, FinancialService $finance)
    {
        $payment->load(['visit.patient', 'visit.procedures.service', 'procedure.service']);
        $visitCharge = $payment->visit ? $finance->visitCharge($payment->visit) : null;
        return view('staff.payments.show', compact('payment', 'visitCharge'));
    }
}
