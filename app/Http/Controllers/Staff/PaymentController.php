<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\InstallmentPlan;
use App\Models\Visit;
use App\Models\Appointment;

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

    private function visitPaid(Visit $visit): float
    {
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
            ->withSum('payments as paid_total', 'amount')
            ->orderByDesc('visit_date')
            ->get()
            ->filter(function (Visit $visit) use ($installmentSet) {
                if (isset($installmentSet[$visit->id])) return false;

                $due = $this->visitDue($visit);
                $paid = (float) ($visit->paid_total ?? 0);
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
            'amount'         => 'required|numeric|min:0',
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

        if ($request->visit_id) {
            $visit = Visit::with(['procedures.service', 'payments'])->findOrFail($request->visit_id);

            if ($visit->procedures->isEmpty()) {
                return back()->withErrors('This visit has no procedures to charge.')->withInput();
            }

            if ($this->hasInstallmentPlanForVisit($visit->id)) {
                return back()->withErrors('This visit is under an installment plan. Please collect payment from the plan instead.')->withInput();
            }

            $due = $this->visitDue($visit);
            $paid = $this->visitPaid($visit);
            $balance = $due - $paid;

            if ($due <= 0 || $balance <= 0) {
                return back()->withErrors('This visit is already fully paid.')->withInput();
            }

            if ($amount < $balance) {
                $desiredFinalTotal = $paid + $amount;
                $this->maybeApplyCustomTotalOverride($visit, $desiredFinalTotal);

                $visit->refresh();
                $visit->load(['procedures.service', 'payments']);
            }

            Payment::create([
                'visit_id'     => $visit->id,
                'amount'       => $amount,
                'method'       => $request->method,
                'payment_date' => $request->payment_date,
            ]);

            $visit->load('payments');
            $this->updateVisitStatusBasedOnPayments($visit);

            return redirect()->route('staff.payments.index')->with('success', 'Cash payment added!');
        }

        if ($request->appointment_id) {
            $payableStatuses = ['scheduled', 'upcoming', 'approved', 'confirmed'];

            $appointment = Appointment::with(['patient', 'service'])->findOrFail($request->appointment_id);

            if (!$appointment->patient_id) {
                return back()->withErrors('This appointment has no patient record yet. Approve it first.')->withInput();
            }

            if (in_array(strtolower((string) $appointment->status), ['completed', 'cancelled', 'declined'], true)) {
                return back()->withErrors('This appointment is not payable (already completed/cancelled/declined).')->withInput();
            }

            if ($appointment->status && !in_array(strtolower((string) $appointment->status), $payableStatuses, true)) {
                return back()->withErrors('This appointment status is not payable.')->withInput();
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

            if ($amount < $balance) {
                $desiredFinalTotal = $paid + $amount;
                $this->maybeApplyCustomTotalOverride($visit, $desiredFinalTotal);

                $visit->refresh();
                $visit->load(['procedures.service', 'payments']);
            }

            Payment::create([
                'visit_id'     => $visit->id,
                'amount'       => $amount,
                'method'       => $request->method,
                'payment_date' => $request->payment_date,
            ]);

            $visit->load(['procedures', 'payments']);
            $this->updateVisitStatusBasedOnPayments($visit);

            $appointment->update(['status' => 'completed']);

            return redirect()->route('staff.payments.index')
                ->with('success', 'Cash payment recorded and appointment marked as completed!');
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
            ->withSum('payments as paid_total', 'amount')
            ->orderByDesc('visit_date')
            ->get()
            ->filter(function (Visit $visit) use ($installmentSet) {
                if (isset($installmentSet[$visit->id])) return false;

                $paid = (float) ($visit->paid_total ?? 0);
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
        // ✅ IMPORTANT: months is required UNLESS open contract is enabled
        $request->validate([
            'visit_id'          => 'nullable|exists:visits,id',
            'appointment_id'    => 'nullable|exists:appointments,id',
            'total_cost'        => 'required|numeric|min:0',
            'downpayment'       => 'required|numeric|min:0',
            'is_open_contract'  => 'nullable|boolean',
            'months'            => 'required_unless:is_open_contract,1|integer|min:1',
            'start_date'        => 'required|date',
        ]);

        if (!$request->visit_id && !$request->appointment_id) {
            return back()->withErrors('Please select a Visit or an Appointment.')->withInput();
        }

        if ($request->visit_id && $request->appointment_id) {
            return back()->withErrors('Please select only one source.')->withInput();
        }

        $isOpen = $request->boolean('is_open_contract');
        $months = $isOpen ? 0 : (int) $request->months; // ✅ never null

        $patientId = null;
        $serviceId = null;
        $visitId   = null;

        if ($request->visit_id) {
            $visit = Visit::with(['patient', 'procedures.service', 'payments'])->findOrFail($request->visit_id);

            if ($this->hasInstallmentPlanForVisit($visit->id)) {
                return back()->withErrors('This visit already has an installment plan.')->withInput();
            }

            if ($visit->procedures->isEmpty()) {
                return back()->withErrors('This visit has no procedures to charge.')->withInput();
            }

            if ($this->visitPaid($visit) > 0) {
                return back()->withErrors('This visit already has cash payments. Please continue cash payments instead of starting an installment plan.')->withInput();
            }

            $patientId = $visit->patient_id;
            $serviceId = optional($visit->procedures->first())->service_id;
            $visitId   = $visit->id;
        }

        if ($request->appointment_id) {
            $appointment = Appointment::with(['patient', 'service'])->findOrFail($request->appointment_id);

            if (!$appointment->patient_id) {
                return back()->withErrors('This appointment has no patient record yet. Approve it first.')->withInput();
            }

            if (in_array(strtolower((string) $appointment->status), ['completed', 'cancelled', 'declined'], true)) {
                return back()->withErrors('This appointment is not payable (already completed/cancelled/declined).')->withInput();
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

        $total   = (float) $request->total_cost;
        $down    = (float) $request->downpayment;
        $balance = $total - $down;

        // ✅ KEY FIX: months is always a number (0 for open contract)
        $plan = InstallmentPlan::create([
            'visit_id'         => $visitId,
            'patient_id'       => $patientId,
            'service_id'       => $serviceId,
            'total_cost'       => $total,
            'downpayment'      => $down,
            'balance'          => $balance,
            'months'           => $months,
            'start_date'       => $request->start_date,
            'status'           => $balance <= 0 ? 'Fully Paid' : 'Partially Paid',
            'is_open_contract' => $isOpen,
        ]);

        // ✅ Store downpayment as Month 0 (cleaner for your shift logic)
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

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
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
            'amount'       => 'required|numeric|min:0',
            'method'       => 'required',
            'payment_date' => 'required|date',
        ]);

        $payment->update($request->only('visit_id', 'amount', 'method', 'payment_date'));

        $visit = Visit::with(['procedures', 'payments'])->find($payment->visit_id);
        if ($visit) {
            $this->updateVisitStatusBasedOnPayments($visit);
        }

        return redirect()->route('staff.payments.index')
            ->with('success', 'Payment updated!');
    }

    public function destroy(Payment $payment)
    {
        $visitId = $payment->visit_id;

        $payment->delete();

        if ($visitId) {
            $visit = Visit::with(['procedures', 'payments'])->find($visitId);
            if ($visit) {
                $this->updateVisitStatusBasedOnPayments($visit);
            }
        }

        return redirect()->route('staff.payments.index')
            ->with('success', 'Payment removed!');
    }

    public function show(Payment $payment)
    {
        $payment->load(['visit.patient', 'visit.procedures.service']);
        return view('staff.payments.show', compact('payment'));
    }
}
