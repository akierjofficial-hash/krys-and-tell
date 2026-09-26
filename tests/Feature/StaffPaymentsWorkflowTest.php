<?php

namespace Tests\Feature;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StaffPaymentsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Patient $patient;
    private Service $service;
    private Visit $visit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->patient = Patient::create(['first_name' => 'Paying', 'last_name' => 'Patient']);
        $this->service = Service::create(['name' => 'Cleaning', 'base_price' => 1000]);
        $this->visit = Visit::create(['patient_id' => $this->patient->id, 'visit_date' => '2026-09-20', 'status' => 'partial']);
        $this->visit->procedures()->create(['service_id' => $this->service->id, 'price' => 1000]);
        $this->actingAs($this->staff);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['patient_id' => $this->patient->id, 'target_type' => 'visit', 'target_id' => $this->visit->id,
            'amount' => 250, 'method' => 'GCash', 'payment_date' => '2026-09-25', 'notes' => 'Receipt 123',
            'submission_token' => (string) Str::uuid()], $overrides);
    }

    private function plan(array $overrides = []): InstallmentPlan
    {
        return InstallmentPlan::create(array_merge(['visit_id' => $this->visit->id, 'patient_id' => $this->patient->id,
            'service_id' => $this->service->id, 'total_cost' => 40000, 'downpayment' => 8000, 'balance' => 32000,
            'months' => 16, 'start_date' => '2026-01-01', 'status' => 'Partially Paid', 'is_open_contract' => false], $overrides));
    }

    public function test_payments_page_has_summary_filters_tabs_and_drawer(): void
    {
        $this->get(route('staff.payments.index'))->assertOk()->assertSeeText('Received today')
            ->assertSeeText('Outstanding')->assertSeeText('Transactions')->assertSeeText('Installment Plans')
            ->assertSeeText('Record Payment')->assertSee('payable-items');
    }

    public function test_payable_items_only_return_real_outstanding_targets_for_patient(): void
    {
        $response = $this->getJson(route('staff.payments.payable-items', ['patient_id' => $this->patient->id]))->assertOk();
        $response->assertJsonPath('items.0.type', 'visit')->assertJsonPath('items.0.balance', 1000);
        Payment::create(['visit_id' => $this->visit->id, 'amount' => 1000, 'method' => 'Cash', 'payment_date' => now()]);
        $this->getJson(route('staff.payments.payable-items', ['patient_id' => $this->patient->id]))->assertJsonCount(0, 'items');
    }

    public function test_visit_payment_preserves_charge_and_does_not_create_a_visit(): void
    {
        $before = Visit::count();
        $this->post(route('staff.payments.record'), $this->payload())->assertRedirect();
        $this->assertDatabaseHas('payments', ['visit_id' => $this->visit->id, 'amount' => 250, 'method' => 'GCash']);
        $this->assertSame($before, Visit::count());
        $this->assertNull($this->visit->fresh()->price);
        $this->assertSame('partial', $this->visit->fresh()->status);
    }

    public function test_overpayment_and_cross_patient_target_are_rejected(): void
    {
        $this->post(route('staff.payments.record'), $this->payload(['amount' => 1000.01]))->assertSessionHasErrors('amount');
        $other = Patient::create(['first_name' => 'Other', 'last_name' => 'Patient']);
        $this->post(route('staff.payments.record'), $this->payload(['patient_id' => $other->id]))->assertSessionHasErrors('target_id');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_duplicate_submission_token_creates_one_receipt(): void
    {
        $payload = $this->payload();
        $this->post(route('staff.payments.record'), $payload)->assertRedirect();
        $this->post(route('staff.payments.record'), $payload)->assertRedirect();
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_plan_collection_counts_downpayment_once_and_creates_no_fake_visit(): void
    {
        $plan = $this->plan();
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'visit_id' => null, 'month_number' => 0,
            'amount' => 8000, 'method' => 'Cash', 'payment_date' => '2026-01-01', 'notes' => 'Downpayment']);
        $before = Visit::count();
        $this->post(route('staff.payments.record'), $this->payload(['target_type' => 'plan', 'target_id' => $plan->id,
            'amount' => 2000]))->assertRedirect();
        $receipt = InstallmentPayment::where('month_number', 1)->firstOrFail();
        $this->assertNull($receipt->visit_id);
        $this->assertSame($before, Visit::count());
        $this->assertEquals(30000, $plan->fresh()->balance);
        $this->assertEquals(10000, $plan->payments()->sum('amount'));
    }

    public function test_unified_ledger_filters_and_paginates_both_payment_types(): void
    {
        $this->post(route('staff.payments.record'), $this->payload())->assertRedirect();
        $plan = $this->plan(['visit_id' => null]);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0, 'amount' => 8000,
            'method' => 'Cash', 'payment_date' => '2026-01-01', 'notes' => 'Downpayment']);
        $this->get(route('staff.payments.index', ['q' => 'Paying']))->assertOk()->assertSee('PAY-00001')->assertSee('INS-00001');
        $this->get(route('staff.payments.index', ['type' => 'downpayment']))->assertOk()->assertDontSee('PAY-00001')->assertSeeText('Downpayment');
    }

    public function test_plan_creation_uses_selected_downpayment_details_and_is_idempotent(): void
    {
        $token = (string) Str::uuid();
        $payload = ['visit_id' => $this->visit->id, 'total_cost' => 40000, 'downpayment' => 8000,
            'months' => 16, 'start_date' => '2026-01-01', 'downpayment_date' => '2026-01-03',
            'downpayment_method' => 'Card', 'submission_token' => $token];
        $this->post(route('staff.payments.store.installment'), $payload)->assertRedirect();
        $this->post(route('staff.payments.store.installment'), $payload)->assertRedirect();
        $this->assertDatabaseCount('installment_plans', 1);
        $this->assertDatabaseCount('installment_payments', 1);
        $this->assertDatabaseHas('installment_payments', ['month_number' => 0, 'amount' => 8000,
            'method' => 'Card', 'visit_id' => null]);
        $this->assertSame('2026-01-03', InstallmentPayment::firstOrFail()->payment_date->toDateString());
        $this->assertEquals(32000, InstallmentPlan::firstOrFail()->balance);
    }

    public function test_legacy_installment_payment_route_now_records_collection_without_fake_visit(): void
    {
        $plan = $this->plan();
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0, 'amount' => 8000,
            'method' => 'Cash', 'payment_date' => '2026-01-01', 'notes' => 'Downpayment']);
        $before = Visit::count();
        $this->post(route('staff.installments.pay.store', $plan), ['month_number' => 1, 'amount' => 2000,
            'method' => 'Cash', 'payment_date' => '2026-02-01'])->assertRedirect();
        $this->assertSame($before, Visit::count());
        $this->assertNull(InstallmentPayment::where('month_number', 1)->firstOrFail()->visit_id);
    }
}
