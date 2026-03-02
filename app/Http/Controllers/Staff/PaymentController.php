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
        $payments = Payment::with(['visit.procedures.service', 'visit.patient'])
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
    public function index()
    {
        $cashPayments = Payment::with(['visit.patient', 'visit.procedures.service'])
            ->whereIn('method', ['Cash', 'GCash', 'Card', 'Bank Transfer'])
            ->get();

        $installments = InstallmentPlan::with(['patient', 'service'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('staff.payments.index', compact('cashPayments', 'installments'));
    }

    public function choosePlan()
    {
        return view('staff.payments.choose-plan');
    }

    // =======================
    // CASH BASIS
    // =======================
    public function createCash()
    {
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

                if ($amount + $epsilon < $balance) {
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
                ]);

                $visit->load('payments');
                $this->updateVisitStatusBasedOnPayments($visit);
            });

            return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'cash'])
                ->with('success', 'Cash payment added!');
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

                if ($amount + $epsilon < $balance) {
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
                ]);

                $visit->load(['procedures', 'payments']);
                $this->updateVisitStatusBasedOnPayments($visit);

                $appointment->update([
                    'status' => $this->visitBalance($visit) <= $epsilon ? 'completed' : 'done',
                ]);
            });

            return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'cash'])
                ->with('success', 'Cash payment recorded and appointment updated!');
        }

        return back()->withErrors('Something went wrong. Please try again.')->withInput();
    }

    // =======================
    // INSTALLMENT BASIS (CREATE FORM)
    // =======================
    public function createInstallment()
    {
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

        return view('staff.payments.installment.create', compact('visits', 'appointments'));
    }

    public function storeInstallment(Request $request)
    {
        $request->validate([
            'visit_id'              => 'nullable|exists:visits,id',
            'appointment_id'        => 'nullable|exists:appointments,id',
            'total_cost'            => 'required|numeric|min:0',
            'downpayment'           => 'required|numeric|min:0',
            'is_open_contract'      => 'nullable|boolean',
            'open_monthly_payment'  => 'required_if:is_open_contract,1|numeric|min:0',
            'months'                => 'required_unless:is_open_contract,1|integer|min:1',
            'start_date'            => 'required|date',
        ]);

        if (!$request->visit_id && !$request->appointment_id) {
            return back()->withErrors('Please select a Visit or an Appointment.')->withInput();
        }

        if ($request->visit_id && $request->appointment_id) {
            return back()->withErrors('Please select only one source.')->withInput();
        }

        $total = (float) $request->total_cost;
        $down  = (float) $request->downpayment;

        if ($down > $total) {
            return back()->withErrors('Downpayment cannot be greater than Total Cost.')->withInput();
        }

        $isOpen = $request->boolean('is_open_contract');
        $months = $isOpen ? 0 : (int) $request->months;
        $openMonthly = $isOpen ? (float) ($request->input('open_monthly_payment') ?? 0) : null;
        $payableStatuses = ['scheduled', 'upcoming', 'approved', 'confirmed'];

        DB::transaction(function () use ($request, $total, $down, $isOpen, $months, $openMonthly, $payableStatuses): void {
            $patientId = null;
            $serviceId = null;
            $visitId = null;

            if ($request->visit_id) {
                $visit = Visit::query()
                    ->with(['patient', 'procedures.service', 'payments'])
                    ->lockForUpdate()
                    ->findOrFail((int) $request->visit_id);

                if ($this->hasInstallmentPlanForVisit($visit->id)) {
                    throw ValidationException::withMessages([
                        'visit_id' => 'This visit already has an installment plan.',
                    ]);
                }

                if ($visit->procedures->isEmpty()) {
                    throw ValidationException::withMessages([
                        'visit_id' => 'This visit has no procedures to charge.',
                    ]);
                }

                if ($this->visitPaid($visit) > 0) {
                    throw ValidationException::withMessages([
                        'visit_id' => 'This visit already has cash payments. Please continue cash payments instead of starting an installment plan.',
                    ]);
                }

                $patientId = $visit->patient_id;
                $serviceId = optional($visit->procedures->first())->service_id;
                $visitId = $visit->id;
            }

            if ($request->appointment_id) {
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

                $patientId = $appointment->patient_id;
                $serviceId = $appointment->service_id;

                $visit = Visit::create([
                    'patient_id' => $patientId,
                    'visit_date' => $request->start_date,
                    'status'     => 'installment',
                ]);

                if ($serviceId) {
                    $visit->procedures()->create([
                        'service_id'   => $serviceId,
                        'tooth_number' => null,
                        'surface'      => null,
                        'shade'        => null,
                        'notes'        => 'From appointment (installment)',
                        'price'        => $appointment->service->base_price ?? 0,
                    ]);
                }

                $visitId = $visit->id;
                $appointment->update(['status' => 'completed']);
            }

            $balance = $total - $down;

            $plan = InstallmentPlan::create([
                'visit_id'              => $visitId,
                'patient_id'            => $patientId,
                'service_id'            => $serviceId,
                'total_cost'            => $total,
                'downpayment'           => $down,
                'balance'               => $balance,
                'months'                => $months,
                'start_date'            => $request->start_date,
                'status'                => $balance <= 0 ? 'Fully Paid' : 'Partially Paid',
                'is_open_contract'      => $isOpen,
                'open_monthly_payment'  => $openMonthly,
            ]);

            if ($down > 0) {
                $hasDp = $plan->payments()->where('month_number', 0)->exists();
                if (!$hasDp) {
                    $plan->payments()->create([
                        'month_number' => 0,
                        'amount'       => $down,
                        'method'       => 'Cash',
                        'payment_date' => $request->start_date,
                        'visit_id'     => $visitId,
                        'notes'        => 'Downpayment',
                    ]);
                }
            }

            if ($visitId) {
                $v = Visit::find($visitId);
                if ($v) {
                    $v->update(['status' => $balance <= 0 ? 'completed' : 'installment']);
                }
            }
        });

        return $this->ktRedirectToReturn($request, 'staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan created!');
    }

    // =======================
    // CASH EDIT / DELETE / SHOW
    // =======================
    public function edit(Payment $payment)
    {
        $payment->loadMissing('visit.patient', 'visit.procedures.service');

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

            $due = $this->visitDue($newVisit);
            $paidWithoutCurrent = (float) Payment::query()
                ->where('visit_id', $newVisitId)
                ->where('id', '!=', $payment->id)
                ->sum('amount');
            $remaining = $due - $paidWithoutCurrent;

            if ($due <= 0 || $remaining <= 0) {
                throw ValidationException::withMessages([
                    'visit_id' => 'Selected visit has no payable balance.',
                ]);
            }

            if ($newAmount > $remaining + $epsilon) {
                throw ValidationException::withMessages([
                    'amount' => 'Updated amount cannot be greater than the remaining balance.',
                ]);
            }

            $payment->update($request->only('visit_id', 'amount', 'method', 'payment_date', 'notes'));

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

    public function show(Payment $payment)
    {
        $payment->load(['visit.patient', 'visit.procedures.service']);
        return view('staff.payments.show', compact('payment'));
    }
}
