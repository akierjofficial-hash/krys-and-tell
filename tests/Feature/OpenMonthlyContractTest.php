<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Imports\InstallmentPaymentsImport;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use App\Services\FinancialService;
use App\Services\OpenMonthlyContractService;
use App\Services\PaymentWorkflowService;
use App\Services\StaffRecordAssistant;
use App\Services\StaffClinicAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OpenMonthlyContractTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Patient $patient;
    private Service $braces;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->patient = Patient::create(['first_name' => 'Monthly', 'last_name' => 'Patient']);
        $this->braces = Service::create(['name' => 'Braces', 'base_price' => 45000]);
        $this->actingAs($this->staff);
    }

    private function plan(): InstallmentPlan
    {
        $visit = Visit::create(['patient_id' => $this->patient->id, 'visit_date' => '2024-01-01', 'status' => 'installment']);
        $visit->procedures()->create(['service_id' => $this->braces->id, 'price' => null]);
        $plan = InstallmentPlan::create([
            'patient_id' => $this->patient->id, 'visit_id' => $visit->id, 'service_id' => $this->braces->id,
            'total_cost' => null, 'balance' => null, 'downpayment' => 7000,
            'is_open_contract' => true, 'is_unpriced_contract' => true,
            'open_monthly_payment' => 2000, 'first_due_date' => '2024-02-01',
            'months' => 0, 'start_date' => '2024-01-01', 'status' => InstallmentPlan::STATUS_PARTIALLY_PAID,
        ]);
        $plan->payments()->create(['month_number' => 0, 'amount' => 7000, 'method' => 'Cash',
            'payment_date' => '2024-01-01', 'notes' => 'Downpayment']);
        return $plan;
    }

    private function collect(InstallmentPlan $plan, float $amount, string $date): void
    {
        app(PaymentWorkflowService::class)->record([
            'patient_id' => $this->patient->id, 'target_type' => 'plan', 'target_id' => $plan->id,
            'amount' => $amount, 'payment_date' => $date, 'method' => 'Cash',
            'submission_token' => (string) Str::uuid(),
        ]);
    }

    public function test_new_contract_from_appointment_stores_unknown_charge_and_first_due_date(): void
    {
        $appointment = Appointment::create(['patient_id' => $this->patient->id, 'service_id' => $this->braces->id,
            'appointment_date' => '2024-01-01', 'status' => 'approved']);
        $this->post(route('staff.installments.store'), [
            'appointment_id' => $appointment->id, 'is_unpriced_contract' => 1,
            'start_date' => '2024-01-01', 'downpayment' => 7000,
            'downpayment_date' => '2024-01-01', 'downpayment_method' => 'Cash',
            'open_monthly_payment' => 2000, 'first_due_date' => '2024-02-01',
            'submission_token' => (string) Str::uuid(),
        ])->assertRedirect()->assertSessionHasNoErrors();
        $plan = InstallmentPlan::firstOrFail();
        $this->assertNull($plan->total_cost);
        $this->assertNull($plan->balance);
        $this->assertNull($plan->visit->procedures->first()->price);
        $this->assertEquals(7000, $plan->payments()->sum('amount'));
        $this->assertSame('2024-02-01', $plan->first_due_date->toDateString());
    }

    public function test_new_plan_form_accepts_an_existing_visit_with_no_agreed_charge(): void
    {
        $visit = Visit::create(['patient_id' => $this->patient->id, 'visit_date' => '2024-01-01']);
        $visit->procedures()->create(['service_id' => $this->braces->id, 'price' => null]);
        $this->get(route('staff.installments.create'))->assertOk()->assertSee('Charge not agreed');
        $this->post(route('staff.installments.store'), [
            'visit_id' => $visit->id, 'is_unpriced_contract' => 1,
            'start_date' => '2024-01-01', 'downpayment' => 7000,
            'downpayment_date' => '2024-01-01', 'downpayment_method' => 'Cash',
            'open_monthly_payment' => 2000, 'first_due_date' => '2024-02-01',
            'submission_token' => (string) Str::uuid(),
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($visit->id, InstallmentPlan::firstOrFail()->visit_id);
        $this->assertDatabaseCount('visits', 1);
    }

    public function test_initial_payment_does_not_create_a_final_balance_or_monthly_credit(): void
    {
        $plan = $this->plan();
        $details = app(OpenMonthlyContractService::class)->details($plan->fresh(), '2024-01-31');
        $this->assertNull(app(FinancialService::class)->planBalance($plan));
        $this->assertNull($plan->balance);
        $this->assertEquals(7000, $details['collected']);
        $this->assertEquals(0, $details['unpaid_due']);
        $this->assertSame('2024-02-01', $details['next_due_date']);
        $this->assertNotSame(InstallmentPlan::STATUS_FULLY_PAID, $plan->status);
    }

    public function test_partial_late_and_advance_receipts_apply_to_oldest_monthly_dues_without_creating_visits(): void
    {
        $plan = $this->plan();
        $this->collect($plan, 500, '2024-02-20');
        $details = app(OpenMonthlyContractService::class)->details($plan->fresh(), '2024-03-15');
        $this->assertEquals(3500, $details['unpaid_due']);
        $this->assertSame('2024-02-01', $details['next_due_date']);

        $this->collect($plan, 5500, '2024-03-20');
        $details = app(OpenMonthlyContractService::class)->details($plan->fresh(), '2024-03-25');
        $this->assertEquals(13000, $details['collected']);
        $this->assertEquals(0, $details['unpaid_due']);
        $this->assertSame('2024-05-01', $details['next_due_date']);
        $this->assertNull($plan->fresh()->balance);
        $this->assertDatabaseCount('visits', 1);
        $this->assertEquals(0, $plan->payments()->whereNotNull('visit_id')->count());
    }

    public function test_excel_monthly_receipt_import_is_collection_only_for_open_contracts(): void
    {
        $plan = $this->plan();
        $import = new InstallmentPaymentsImport($plan);
        $import->collection(collect([
            ['month_number' => 1, 'amount' => 2000, 'payment_date' => '2024-02-01', 'method' => 'Cash'],
            ['month_number' => 2, 'amount' => 1500, 'payment_date' => '2024-03-01', 'method' => 'GCash'],
        ]));
        $this->assertSame(2, $import->created);
        $this->assertDatabaseCount('visits', 1);
        $this->assertEquals(0, $plan->payments()->whereNotNull('visit_id')->count());
        $this->assertEquals(10500, $plan->payments()->sum('amount'));
    }

    public function test_close_requires_review_of_unpaid_dues_and_keeps_unknown_final_balance(): void
    {
        $plan = $this->plan();
        $route = route('staff.installments.complete', $plan);
        $this->post($route, ['ended_at' => '2024-03-15', 'closure_note' => 'Treatment ended'])->assertSessionHasErrors('acknowledge_unpaid');
        $this->post($route, ['ended_at' => '2024-03-15', 'closure_note' => 'Treatment ended', 'acknowledge_unpaid' => 1])->assertRedirect();
        $this->assertSame(InstallmentPlan::STATUS_COMPLETED, $plan->fresh()->status);
        $this->assertSame('2024-03-15', $plan->fresh()->ended_at->toDateString());
        $this->assertNull($plan->fresh()->balance);
        $this->assertEquals(4000, app(OpenMonthlyContractService::class)->details($plan->fresh(), '2024-06-01')['unpaid_due']);
        $this->assertDatabaseHas('activity_logs', ['event' => 'open_contract_closed', 'user_id' => $this->staff->id]);
        $this->post(route('staff.installments.reopen', $plan))->assertRedirect();
        $this->assertNull($plan->fresh()->ended_at);
        $this->assertDatabaseHas('activity_logs', ['event' => 'open_contract_reopened', 'user_id' => $this->staff->id]);
    }

    public function test_later_agreed_total_is_explicit_and_audited_while_fixed_plans_stay_unchanged(): void
    {
        $plan = $this->plan();
        $this->collect($plan, 2000, '2024-02-01');
        $this->post(route('staff.installments.agree-total', $plan), ['total_cost' => 8500, 'reason' => 'Signed agreement'])->assertSessionHasErrors('total_cost');
        $this->post(route('staff.installments.agree-total', $plan), ['total_cost' => 30000, 'reason' => 'Signed agreement'])->assertRedirect();
        $this->assertEquals(21000, app(FinancialService::class)->planBalance($plan->fresh()));
        $this->assertNotNull($plan->fresh()->total_agreed_at);
        $this->assertSame($this->staff->id, $plan->fresh()->total_agreed_by);
        $this->assertDatabaseHas('activity_logs', ['event' => 'open_contract_total_agreed', 'user_id' => $this->staff->id]);
        $import = new InstallmentPaymentsImport($plan->fresh());
        $import->collection(collect([['month_number' => 2, 'amount' => 22000,
            'payment_date' => '2024-03-01', 'method' => 'Cash']]));
        $this->assertSame(1, $import->skipped);
        $this->assertEquals(9000, $plan->payments()->sum('amount'));
        $this->post(route('staff.installments.complete', $plan), ['ended_at' => '2024-03-15',
            'closure_note' => 'Treatment ended', 'acknowledge_unpaid' => 1])->assertRedirect();
        $this->assertSame('2024-03-15', $plan->fresh()->ended_at->toDateString());

        $fixed = InstallmentPlan::create(['patient_id' => $this->patient->id, 'service_id' => $this->braces->id,
            'total_cost' => 40000, 'balance' => 40000, 'downpayment' => 8000,
            'months' => 12, 'start_date' => '2024-01-01', 'status' => InstallmentPlan::STATUS_PENDING]);
        app(FinancialService::class)->recomputePlan($fixed);
        $this->assertEquals(32000, $fixed->fresh()->balance);
    }

    public function test_pages_reports_and_assistant_do_not_fabricate_a_final_balance(): void
    {
        $plan = $this->plan();
        $this->get(route('staff.installments.show', $plan))->assertOk()->assertSee('Not determinable');
        $this->get(route('staff.visits.show', $plan->visit))->assertOk()->assertSee('financed charge not agreed');
        $this->get(route('staff.patients.show', $this->patient))->assertOk()->assertSee('Not determinable');
        $this->get(route('staff.payments.index', ['tab' => 'plans']))->assertOk()->assertSee('Not determinable');
        $answer = app(StaffRecordAssistant::class)->answer($this->patient, 'What is the remaining balance?');
        $this->assertStringContainsString('Not determinable', $answer['message']);
        $overview = app(StaffClinicAssistant::class)->balanceOverview();
        $this->assertSame(1, $overview['incomplete_count']);
        $this->assertEquals(0, $overview['total']);
        $summary = app(FinancialService::class)->summary();
        $this->assertEquals(0, $summary['outstanding']);
        $this->assertSame(1, $summary['unknown_total_plans']);
    }
}
