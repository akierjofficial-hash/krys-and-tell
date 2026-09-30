<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\InstallmentPlan;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallmentPlanCreationService
{
    public function __construct(private FinancialService $finance) {}

    public function create(array $data): InstallmentPlan
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['submission_token']) && $existing = InstallmentPlan::where('submission_token', $data['submission_token'])->first()) return $existing;
            $unknown = (bool) ($data['is_unpriced_contract'] ?? false);
            $visit = !empty($data['visit_id']) ? Visit::with(['procedures', 'payments'])->lockForUpdate()->findOrFail($data['visit_id'])
                : $this->visitFromAppointment((int) $data['appointment_id'], $data['start_date'], $unknown);
            if (InstallmentPlan::where('visit_id', $visit->id)->exists()) $this->invalid('visit_id', 'This visit already has an installment plan.');
            if ($visit->procedures->isEmpty()) $this->invalid('visit_id', 'This visit has no treatment to finance.');
            if ($this->finance->visitPaid($visit) > 0) $this->invalid('visit_id', 'This visit already has ordinary payments. Continue that payment flow instead.');
            if (!$unknown && $visit->procedures->every(fn ($procedure) => $procedure->price === null)) {
                $this->invalid('visit_id', 'This visit has no agreed charge. Select the open monthly contract arrangement or record an agreed total.');
            }
            if ($unknown && $visit->procedures->contains(fn ($procedure) => $procedure->price !== null)) {
                $this->invalid('visit_id', 'This visit has a recorded procedure charge. Use a visit with an unknown charge for a no-total contract.');
            }
            $total = $unknown ? null : (float) $data['total_cost'];
            $down = (float) $data['downpayment'];
            if ($total !== null && $down > $total) $this->invalid('downpayment', 'Downpayment cannot exceed the agreed cost.');
            $open = $unknown || (bool) ($data['is_open_contract'] ?? false);
            $plan = InstallmentPlan::create([
                'visit_id' => $visit->id, 'patient_id' => $visit->patient_id,
                'service_id' => $visit->procedures->first()?->service_id, 'total_cost' => $total,
                'downpayment' => $down, 'balance' => $total, 'months' => $open ? 0 : (int) $data['months'],
                'start_date' => $data['start_date'], 'status' => InstallmentPlan::STATUS_PENDING,
                'is_open_contract' => $open, 'open_monthly_payment' => $open ? (float) $data['open_monthly_payment'] : null,
                'is_unpriced_contract' => $unknown, 'first_due_date' => $unknown ? $data['first_due_date'] : null,
                'submission_token' => $data['submission_token'] ?? null,
            ]);
            if ($down > 0) $plan->payments()->create(['visit_id' => null, 'month_number' => 0, 'amount' => $down,
                'method' => $data['downpayment_method'], 'payment_date' => $data['downpayment_date'], 'notes' => 'Downpayment']);
            $this->finance->recomputePlan($plan);
            $visit->update(['status' => $plan->hasUnknownTotal() ? 'installment' : ($plan->balance <= 0 ? 'completed' : 'installment')]);
            return $plan;
        }, 3);
    }

    private function visitFromAppointment(int $id, string $date, bool $unknown = false): Visit
    {
        $appointment = Appointment::with('service')->lockForUpdate()->findOrFail($id);
        if (!$appointment->patient_id) $this->invalid('appointment_id', 'This appointment has no patient record.');
        if (in_array(strtolower((string) $appointment->status), ['completed', 'cancelled', 'declined'], true)) $this->invalid('appointment_id', 'This appointment is no longer payable.');
        $visit = Visit::create(['patient_id' => $appointment->patient_id, 'source_appointment_id' => $appointment->id, 'doctor_id' => $appointment->doctor_id,
            'dentist_name' => $appointment->dentist_name, 'visit_date' => $date, 'status' => 'installment',
            'notes' => 'Installment plan created from appointment']);
        if ($appointment->service_id) $visit->procedures()->create(['service_id' => $appointment->service_id,
            'price' => $unknown ? null : ($appointment->service?->base_price ?? 0)]);
        $appointment->update(['status' => 'completed']);
        return $visit->load(['procedures', 'payments']);
    }

    private function invalid(string $key, string $message): never
    {
        throw ValidationException::withMessages([$key => $message]);
    }
}
