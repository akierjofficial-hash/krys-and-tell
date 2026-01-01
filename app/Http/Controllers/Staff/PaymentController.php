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
    // MAIN PAGE
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
        $visits = Visit::with(['patient', 'procedures.service'])
            ->whereHas('procedures')
            ->latest()
            ->get();

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
            return back()->withErrors('Please select a Visit or an Appointment.');
        }

        if ($request->visit_id && $request->appointment_id) {
            return back()->withErrors('Please select only one source.');
        }

        $amount = $request->amount;

        if ($request->visit_id) {
            $visit = Visit::findOrFail($request->visit_id);

            Payment::create([
                'visit_id'     => $visit->id,
                'amount'       => $amount,
                'method'       => $request->method,
                'payment_date' => $request->payment_date,
            ]);

            $visit->update(['status' => 'completed']);

            return redirect()->route('staff.payments.index')->with('success', 'Cash payment added!');
        }

        if ($request->appointment_id) {
            $appointment = Appointment::with(['service'])->findOrFail($request->appointment_id);

            if (!$appointment->patient_id) {
                return back()->withErrors('This appointment has no patient record yet. Approve it first.');
            }

            $visit = Visit::create([
                'patient_id' => $appointment->patient_id,
                'visit_date' => now()->toDateString(),
                'status'     => 'completed',
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

            Payment::create([
                'visit_id'     => $visit->id,
                'amount'       => $amount,
                'method'       => $request->method,
                'payment_date' => $request->payment_date,
            ]);

            $appointment->update(['status' => 'completed']);

            return redirect()->route('staff.payments.index')
                ->with('success', 'Cash payment recorded and appointment marked as completed!');
        }

        return back()->withErrors('Something went wrong. Please try again.');
    }

    // =======================
    // INSTALLMENT BASIS (CREATE FORM)
    // =======================
    public function createInstallment()
    {
        $visits = Visit::with(['patient', 'procedures.service'])
            ->whereDoesntHave('payments')
            ->latest()
            ->get();

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
            'visit_id'       => 'nullable|exists:visits,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'total_cost'     => 'required|numeric|min:0',
            'downpayment'    => 'required|numeric|min:0',
            'months'         => 'required|integer|min:1',
            'start_date'     => 'required|date',
        ]);

        if (!$request->visit_id && !$request->appointment_id) {
            return back()->withErrors('Please select a Visit or an Appointment.')->withInput();
        }

        if ($request->visit_id && $request->appointment_id) {
            return back()->withErrors('Please select only one source.')->withInput();
        }

        $patientId = null;
        $serviceId = null;

        if ($request->visit_id) {
            $visit = Visit::with(['patient', 'procedures.service'])->findOrFail($request->visit_id);

            $patientId = $visit->patient_id;
            $serviceId = optional($visit->procedures->first())->service_id;

            $visit->update(['status' => 'completed']);
        }

        if ($request->appointment_id) {
            $appointment = Appointment::with(['patient', 'service'])->findOrFail($request->appointment_id);

            if (!$appointment->patient_id) {
                return back()->withErrors('This appointment has no patient record yet. Approve it first.')->withInput();
            }

            $patientId = $appointment->patient_id;
            $serviceId = $appointment->service_id;

       
            $appointment->update(['status' => 'completed']);
        }

        $balance = $request->total_cost - $request->downpayment;

        InstallmentPlan::create([
            'visit_id'    => $request->visit_id, 
            'patient_id'  => $patientId,
            'service_id'  => $serviceId,
            'total_cost'  => $request->total_cost,
            'downpayment' => $request->downpayment,
            'balance'     => $balance,
            'months'      => $request->months,
            'start_date'  => $request->start_date,
            'status'      => $balance <= 0 ? 'Fully Paid' : 'Partially Paid',
        ]);

        return redirect()->route('staff.payments.index', ['tab' => 'installment'])
            ->with('success', 'Installment plan created!');
    }

    // =======================
    // CASH EDIT / DELETE / SHOW
    // =======================
    public function edit(\App\Models\Payment $payment)
    {
        $payment->loadMissing('visit.patient', 'visit.procedures.service');

        $visits = \App\Models\Visit::with(['patient', 'procedures.service'])
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

        return redirect()->route('staff.payments.index')
            ->with('success', 'Payment updated!');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('staff.payments.index')
            ->with('success', 'Payment removed!');
    }

    public function show(Payment $payment)
    {
        $payment->load(['visit.patient', 'visit.procedures.service']);
        return view('staff.payments.show', compact('payment'));
    }
}
