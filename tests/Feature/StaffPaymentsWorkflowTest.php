<?php

namespace Tests\Feature;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use App\Services\PaymentTransactionService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            ->assertSeeText('Record Payment')->assertSee('payable-items')
            ->assertViewHas('plans', null);
        $this->get(route('staff.payments.index', ['tab' => 'plans']))->assertOk()
            ->assertViewHas('transactions', null);
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

    public function test_ledger_balance_after_uses_loaded_receipts_without_per_row_sum_queries(): void
    {
        $first = Payment::create(['visit_id' => $this->visit->id, 'amount' => 200,
            'method' => 'Cash', 'payment_date' => '2026-09-20']);
        $second = Payment::create(['visit_id' => $this->visit->id, 'amount' => 100,
            'method' => 'GCash', 'payment_date' => '2026-09-21']);
        $plan = $this->plan(['visit_id' => null, 'downpayment' => 0]);
        $planFirst = InstallmentPayment::create(['installment_plan_id' => $plan->id,
            'month_number' => 1, 'amount' => 2000, 'method' => 'Cash', 'payment_date' => '2026-09-20']);
        $planSecond = InstallmentPayment::create(['installment_plan_id' => $plan->id,
            'month_number' => 2, 'amount' => 3000, 'method' => 'Cash', 'payment_date' => '2026-09-21']);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $rows = app(PaymentTransactionService::class)->paginate(Request::create('/staff/payments'))
            ->getCollection()->keyBy(fn ($row) => $row->source.':'.$row->source_id);
        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $this->assertEquals(800, $rows['ordinary:'.$first->id]->balance_after);
        $this->assertEquals(700, $rows['ordinary:'.$second->id]->balance_after);
        $this->assertEquals(38000, $rows['installment:'.$planFirst->id]->balance_after);
        $this->assertEquals(35000, $rows['installment:'.$planSecond->id]->balance_after);
        $this->assertFalse($queries->contains(fn ($sql) => preg_match('/select\s+sum\s*\(/i', $sql)));
    }

    public function test_text_search_does_not_compare_numeric_payment_or_plan_ids_to_patient_name(): void
    {
        $this->post(route('staff.payments.record'), $this->payload())->assertRedirect();
        $plan = $this->plan(['visit_id' => null]);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0,
            'amount' => 8000, 'method' => 'Cash', 'payment_date' => '2026-01-01', 'notes' => 'Downpayment']);

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            if (str_contains($query->sql, 'installment_plans') || str_contains($query->sql, 'from (')) {
                $queries[] = $query->sql;
            }
        });

        $filters = ['tab' => 'transactions', 'q' => 'Paying', 'patient_id' => '', 'type' => '',
            'method' => '', 'status' => '', 'date_from' => '', 'date_to' => ''];
        $this->get(route('staff.payments.index', $filters))->assertOk()
            ->assertSee('PAY-00001')->assertSee('INS-00001');
        $this->get(route('staff.payments.index', array_merge($filters, ['q' => 'PAYING'])))
            ->assertOk()->assertSee('PAY-00001')->assertSee('INS-00001');
        $this->get(route('staff.payments.index', array_merge($filters, ['q' => 'No such patient'])))
            ->assertOk()->assertSeeText('No transactions match these filters.');
        $this->get(route('staff.payments.index', array_merge($filters, ['q' => 'PAY-00001'])))
            ->assertOk()->assertSee('PAY-00001')->assertDontSee('INS-00001');
        $this->get(route('staff.payments.index', array_merge($filters, ['q' => 'INS-00001'])))
            ->assertOk()->assertSee('INS-00001')->assertDontSee('PAY-00001');
        $this->get(route('staff.payments.index', array_merge($filters, ['tab' => 'plans'])))
            ->assertOk()->assertSeeText('Paying')->assertSeeText('Plan #');

        foreach ($queries as $sql) {
            $this->assertDoesNotMatchRegularExpression('/["`]tx["`]\.["`]id["`]\s+like\b/i', $sql);
            $this->assertDoesNotMatchRegularExpression('/["`]installment_plans["`]\.["`]id["`]\s*=\s*\?/i', $sql);
        }
    }

    public function test_payment_search_preserves_combined_filters_and_pagination(): void
    {
        foreach (range(1, 22) as $day) {
            Payment::create(['visit_id' => $this->visit->id, 'amount' => 1, 'method' => 'Cash',
                'payment_date' => sprintf('2026-09-%02d', $day)]);
        }
        Payment::create(['visit_id' => $this->visit->id, 'amount' => 1, 'method' => 'GCash',
            'payment_date' => '2026-09-15']);

        $filters = ['tab' => 'transactions', 'q' => 'Paying', 'type' => 'ordinary',
            'method' => 'Cash', 'date_from' => '2026-09-01', 'date_to' => '2026-09-30', 'sort' => 'oldest'];
        $this->get(route('staff.payments.index', $filters))->assertOk()
            ->assertViewHas('transactions', fn ($page) => $page->total() === 22 && $page->count() === 20);
        $this->get(route('staff.payments.index', array_merge($filters, ['page' => 2])))->assertOk()
            ->assertViewHas('transactions', fn ($page) => $page->total() === 22 && $page->count() === 2);
        $this->get(route('staff.payments.index', array_merge($filters, ['method' => 'Card'])))
            ->assertOk()->assertSeeText('No transactions match these filters.');
    }

    public function test_switching_tabs_keeps_patient_search_but_drops_tab_specific_filters(): void
    {
        $response = $this->get(route('staff.payments.index', ['tab' => 'transactions', 'q' => 'Paying',
            'patient_id' => $this->patient->id, 'status' => 'partial', 'method' => 'Cash',
            'date_from' => '2026-09-01', 'sort' => 'oldest']))->assertOk();
        $response->assertSee(e(route('staff.payments.index', ['q' => 'Paying',
            'patient_id' => $this->patient->id, 'date_from' => '2026-09-01', 'tab' => 'plans'])), false);
    }

    public function test_installment_patient_sort_uses_names_instead_of_patient_ids(): void
    {
        $zulu = Patient::create(['first_name' => 'Zed', 'last_name' => 'Zulu']);
        $alpha = Patient::create(['first_name' => 'Amy', 'last_name' => 'Alpha']);
        $zuluVisit = Visit::create(['patient_id' => $zulu->id, 'visit_date' => '2026-09-20']);
        $alphaVisit = Visit::create(['patient_id' => $alpha->id, 'visit_date' => '2026-09-20']);
        $zuluPlan = $this->plan(['patient_id' => $zulu->id, 'visit_id' => $zuluVisit->id]);
        $alphaPlan = $this->plan(['patient_id' => $alpha->id, 'visit_id' => $alphaVisit->id]);
        $patientPlan = $this->plan();

        $this->get(route('staff.payments.index', ['tab' => 'plans', 'sort' => 'patient']))
            ->assertOk()->assertViewHas('plans', fn ($page) => $page->pluck('id')->all() ===
                [$alphaPlan->id, $patientPlan->id, $zuluPlan->id]);
    }

    public function test_treatment_search_finds_receipt_without_a_procedure_id_using_its_displayed_visit_treatment(): void
    {
        Payment::create(['visit_id' => $this->visit->id, 'amount' => 250,
            'method' => 'Cash', 'payment_date' => '2026-09-25']);
        $this->get(route('staff.payments.index', ['tab' => 'transactions', 'q' => 'CLEANING']))
            ->assertOk()->assertViewHas('transactions', fn ($page) => $page->total() === 1)
            ->assertSeeText('Cleaning');
    }

    public function test_legacy_installment_list_redirects_to_working_plan_search(): void
    {
        $this->get(route('staff.installments.index', ['search' => 'PAYING']))
            ->assertRedirect(route('staff.payments.index', ['tab' => 'plans', 'q' => 'PAYING']));
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
