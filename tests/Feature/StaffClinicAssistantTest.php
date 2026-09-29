<?php

namespace Tests\Feature;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffClinicAssistantTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff', 'is_active' => true]));
    }

    private function patient(string $name): Patient
    {
        return Patient::create(['first_name' => $name, 'last_name' => 'Test']);
    }

    public function test_only_active_staff_can_ask_clinic_wide_questions(): void
    {
        $question = ['question' => 'Which patients have outstanding balances?'];
        $this->postJson(route('staff.assistant.ask'), $question)->assertUnauthorized();
        foreach (['admin', 'user'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => true]));
            $this->postJson(route('staff.assistant.ask'), $question)->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['role' => 'staff', 'is_active' => false]));
        $this->postJson(route('staff.assistant.ask'), $question)->assertForbidden();
    }

    public function test_clinic_questions_do_not_require_a_selected_patient(): void
    {
        $this->staff();
        $patient = $this->patient('Clinic');
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-29', 'price' => 100]);
        Payment::create(['visit_id' => $visit->id, 'amount' => 40, 'method' => 'Cash', 'payment_date' => '2026-09-29']);
        $this->travelTo(CarbonImmutable::parse('2026-09-29 09:00', 'Asia/Manila')->utc());

        $this->postJson(route('staff.assistant.ask'), ['scope' => 'clinic', 'question' => 'How much did the clinic collect today?'])
            ->assertOk()->assertJsonPath('scope', 'clinic')->assertJsonPath('record_count', 1)->assertJsonPath('total', 40);
        $this->postJson(route('staff.assistant.ask'), ['scope' => 'clinic', 'question' => 'What is the clinic balance?'])
            ->assertOk()->assertJsonPath('scope', 'clinic')->assertJsonPath('total', 60);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'What is the total balance?'])
            ->assertOk()->assertJsonMissingPath('scope')->assertSee('60.00');
        $this->postJson(route('staff.assistant.ask'), ['scope' => 'clinic', 'question' => 'Tell me about the clinic'])
            ->assertOk()->assertSee('supported clinic-wide record question')->assertDontSee('Select a patient for individual records');
        $this->postJson(route('staff.assistant.ask'), ['question' => 'What is the balance?'])
            ->assertUnprocessable();
    }

    public function test_outstanding_counts_each_patient_once_and_financed_visits_only_through_plans(): void
    {
        $this->staff();
        $one = $this->patient('One');
        $two = $this->patient('Two');
        $paid = $this->patient('Paid');
        $ordinary = Visit::create(['patient_id' => $one->id, 'visit_date' => '2026-09-01', 'price' => 1000, 'status' => 'partial']);
        Payment::create(['visit_id' => $ordinary->id, 'amount' => 250, 'method' => 'Cash', 'payment_date' => '2026-09-01']);
        $financed = Visit::create(['patient_id' => $one->id, 'visit_date' => '2026-09-02', 'price' => 40000, 'status' => 'installment']);
        $plan = InstallmentPlan::create(['patient_id' => $one->id, 'visit_id' => $financed->id,
            'total_cost' => 40000, 'downpayment' => 8000, 'balance' => 32000, 'months' => 16, 'start_date' => '2026-09-01']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0,
            'amount' => 8000, 'method' => 'Cash', 'payment_date' => '2026-09-01', 'notes' => 'Downpayment']);
        Visit::create(['patient_id' => $two->id, 'visit_date' => '2026-09-02', 'price' => 300, 'status' => 'partial']);
        $fullyPaid = Visit::create(['patient_id' => $paid->id, 'visit_date' => '2026-09-02', 'price' => 200, 'status' => 'completed']);
        Payment::create(['visit_id' => $fullyPaid->id, 'amount' => 200, 'method' => 'Cash', 'payment_date' => '2026-09-02']);

        $this->postJson(route('staff.assistant.ask'), ['question' => 'Which patients have outstanding balances?'])
            ->assertOk()->assertJsonPath('scope', 'clinic')->assertJsonPath('record_count', 2)
            ->assertJsonPath('total', 33050)
            ->assertJsonFragment(['url' => route('staff.patients.show', ['patient' => $one, 'tab' => 'tab-payments'])]);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_collections_use_clinic_day_and_monday_sunday_week_boundaries_and_deleted_receipts_are_excluded(): void
    {
        $this->staff();
        $this->travelTo(CarbonImmutable::parse('2026-09-29 00:30:00', 'Asia/Manila')->utc());
        $patient = $this->patient('Collector');
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-29', 'price' => 1000]);
        foreach ([['2026-09-27', 50], ['2026-09-28', 100], ['2026-09-29', 200], ['2026-10-04', 300], ['2026-10-05', 400]] as [$date, $amount]) {
            Payment::create(['visit_id' => $visit->id, 'amount' => $amount, 'method' => 'Cash', 'payment_date' => $date]);
        }
        $deleted = Payment::create(['visit_id' => $visit->id, 'amount' => 900, 'method' => 'Cash', 'payment_date' => '2026-09-29']);
        $deleted->delete();
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'total_cost' => 500, 'downpayment' => 0,
            'balance' => 500, 'months' => 1, 'start_date' => '2026-09-01']);
        $installment = InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 1,
            'amount' => 150, 'method' => 'Cash', 'payment_date' => '2026-09-29']);

        $this->postJson(route('staff.assistant.ask'), ['question' => 'What payments were received today?'])
            ->assertOk()->assertJsonPath('date_range.from', '2026-09-29')->assertJsonPath('record_count', 2)
            ->assertJsonPath('total', 350)
            ->assertJsonFragment(['url' => route('staff.installments.show', $plan).'#installment-payment-'.$installment->id]);
        $this->get(route('staff.payments.index', ['date_from' => '2026-09-29', 'date_to' => '2026-09-29']))
            ->assertOk()->assertViewHas('transactions', fn ($rows) => $rows->total() === 2
                && (float) $rows->getCollection()->sum('amount') === 350.0);
        $this->postJson(route('staff.assistant.ask'), ['question' => 'What payments were received this week?'])
            ->assertOk()->assertJsonPath('date_range.from', '2026-09-28')
            ->assertJsonPath('date_range.to', '2026-10-04')->assertJsonPath('record_count', 4)
            ->assertJsonPath('total', 750);
    }

    public function test_due_counts_only_unreceipted_months_on_or_before_clinic_today(): void
    {
        $this->staff();
        $this->travelTo(CarbonImmutable::parse('2026-09-29 00:30:00', 'Asia/Manila')->utc());
        $patient = $this->patient('Due');
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'total_cost' => 5000,
            'downpayment' => 1000, 'balance' => 4000, 'months' => 3, 'start_date' => '2026-07-29', 'status' => 'Partially Paid']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0,
            'amount' => 1000, 'method' => 'Cash', 'payment_date' => '2026-07-29', 'notes' => 'Downpayment']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 1,
            'amount' => 500, 'method' => 'Cash', 'payment_date' => '2026-08-29']); // Partial receipt still marks month 1 paid in existing UI.
        $overdue = InstallmentPlan::create(['patient_id' => $patient->id, 'total_cost' => 1000,
            'downpayment' => 0, 'balance' => 1000, 'months' => 1, 'start_date' => '2026-08-01', 'status' => 'Pending']);
        InstallmentPlan::create(['patient_id' => $patient->id, 'total_cost' => 2000, 'downpayment' => 0,
            'balance' => 2000, 'months' => 0, 'start_date' => '2026-07-29', 'is_open_contract' => true, 'status' => 'Pending']);

        $this->postJson(route('staff.assistant.ask'), ['question' => 'Which installment payments are due or overdue?'])
            ->assertOk()->assertJsonPath('record_count', 2)->assertJsonPath('total', 4500)
            ->assertSee('1 overdue, 1 due today')->assertSee('1 open or undated plan')
            ->assertSee('even if the payment was partial')
            ->assertJsonFragment(['url' => route('staff.installments.show', $plan).'#installment-schedule'])
            ->assertJsonFragment(['url' => route('staff.installments.show', $overdue).'#installment-schedule']);
        $this->assertDatabaseCount('installment_plans', 3);
    }

    public function test_visit_range_matches_all_visits_page_and_requires_dates(): void
    {
        $this->staff();
        $patient = $this->patient('Visitor');
        $first = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'status' => 'completed', 'price' => 100]);
        $last = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-30', 'status' => 'completed', 'price' => 100]);
        Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-10-01', 'status' => 'completed', 'price' => 100]);
        $paymentOnly = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-10', 'status' => 'installment']);
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'total_cost' => 100, 'downpayment' => 0,
            'balance' => 100, 'months' => 1, 'start_date' => '2026-09-01']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'visit_id' => $paymentOnly->id,
            'month_number' => 1, 'amount' => 50, 'method' => 'Cash', 'payment_date' => '2026-09-10']);
        $deleted = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-20', 'status' => 'partial', 'price' => 100]);
        $deleted->delete();
        $question = ['question' => 'How many visits occurred in the selected date range?'];
        $this->postJson(route('staff.assistant.ask'), $question)->assertUnprocessable();
        $this->postJson(route('staff.assistant.ask'), $question + ['date_from' => '2026-10-01', 'date_to' => '2026-09-01'])->assertUnprocessable();
        $this->postJson(route('staff.assistant.ask'), $question + ['date_from' => '2026-09-01', 'date_to' => '2026-09-30'])
            ->assertOk()->assertJsonPath('record_count', 3)->assertJsonPath('date_range.from', '2026-09-01')
            ->assertJsonFragment(['url' => route('staff.visits.show', $first)])
            ->assertJsonFragment(['url' => route('staff.visits.show', $last)]);
        $this->get(route('staff.visits.index', ['view' => 'all', 'date_from' => '2026-09-01', 'date_to' => '2026-09-30']))
            ->assertOk()->assertViewHas('visits', fn ($rows) => $rows->count() === 3);
    }

    public function test_large_outstanding_answer_limits_links_but_keeps_full_count_and_total(): void
    {
        $this->staff();
        for ($number = 1; $number <= 12; $number++) {
            $patient = $this->patient('Listed'.$number);
            Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'price' => 100]);
        }
        $this->postJson(route('staff.assistant.ask'), ['question' => 'Which patients have outstanding balances?'])
            ->assertOk()->assertJsonPath('record_count', 12)->assertJsonPath('total', 1200)
            ->assertJsonCount(10, 'links')->assertSee('Showing the first 10 supporting records.');
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_explicit_today_and_date_range_questions_remain_clinic_wide_with_patient_selected(): void
    {
        $this->staff();
        $this->travelTo(CarbonImmutable::parse('2026-09-29 09:00', 'Asia/Manila')->utc());
        $selected = $this->patient('Selected');
        $other = $this->patient('Other');
        $visit = Visit::create(['patient_id' => $other->id, 'visit_date' => '2026-09-29', 'price' => 100]);
        Payment::create(['visit_id' => $visit->id, 'amount' => 100, 'method' => 'Cash', 'payment_date' => '2026-09-29']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $selected->id,
            'question' => 'What payments were received today?'])
            ->assertOk()->assertJsonPath('scope', 'clinic')->assertJsonPath('total', 100)
            ->assertSee('Across the clinic');
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $selected->id,
            'question' => 'How many visits occurred in the selected date range?',
            'date_from' => '2026-09-29', 'date_to' => '2026-09-29'])
            ->assertOk()->assertJsonPath('scope', 'clinic')->assertJsonPath('record_count', 1);
    }

    public function test_archived_source_receipt_is_counted_by_existing_collections_but_ledger_difference_is_disclosed(): void
    {
        $this->staff();
        $this->travelTo(CarbonImmutable::parse('2026-09-29 09:00', 'Asia/Manila')->utc());
        $patient = $this->patient('Archived');
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-29', 'price' => 100]);
        Payment::create(['visit_id' => $visit->id, 'amount' => 100, 'method' => 'Cash', 'payment_date' => '2026-09-29']);
        $visit->delete();
        $this->postJson(route('staff.assistant.ask'), ['question' => 'What payments were received today?'])
            ->assertOk()->assertJsonPath('record_count', 1)->assertJsonPath('total', 100)
            ->assertSee('hidden from the staff Payments ledger');
        $this->get(route('staff.payments.index', ['date_from' => '2026-09-29', 'date_to' => '2026-09-29']))
            ->assertOk()->assertViewHas('transactions', fn ($rows) => $rows->total() === 0);
    }
}
