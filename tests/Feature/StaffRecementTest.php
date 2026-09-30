<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use App\Services\FinancialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StaffRecementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Patient $patient;
    private Doctor $doctor;
    private Service $recement;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->patient = Patient::create(['first_name' => 'Dental', 'last_name' => 'Patient']);
        $this->doctor = Doctor::create(['name' => 'Dr. Recement', 'is_active' => true]);
        $this->recement = Service::where('internal_code', 'recement')->firstOrFail();
        $this->actingAs($this->staff);
    }

    private function row(string $price = '500.00', array $payments = [], ?string $context = null): array
    {
        return [
            'visit_date' => '2024-01-10',
            'doctor_id' => $this->doctor->id,
            'arrangement' => 'ordinary',
            'procedures' => [[
                'service_id' => $this->recement->id,
                'price' => $price,
                'related_context' => $context,
            ]],
            'payments' => $payments,
        ];
    }

    private function saveRow(array $row): Visit
    {
        $batch = (string) Str::uuid();
        $review = $this->postJson(route('staff.records.review', $batch), [
            'patient_id' => $this->patient->id,
            'mode' => 'past',
            'version' => 0,
            'payload' => ['visits' => [$row]],
        ])->assertOk();
        $this->postJson(route('staff.records.store', $batch), [
            'review_hash' => $review->json('review_hash'),
            'acknowledge_duplicates' => false,
        ])->assertOk();

        return Visit::latest('id')->firstOrFail();
    }

    public function test_recement_is_staff_only_and_has_500_peso_default(): void
    {
        $this->assertEquals(500, $this->recement->base_price);
        $this->assertTrue($this->recement->is_staff_only);
        $this->get(route('public.services.index'))->assertOk()->assertDontSee('Recement');
        $this->get(route('public.services.show', $this->recement))->assertNotFound();
        $this->get(route('public.booking.create', $this->recement))->assertNotFound();
        $this->get(route('public.booking.slots', $this->recement))->assertNotFound();
        $this->get(route('staff.records.index', ['patient_id' => $this->patient->id, 'mode' => 'visit']))
            ->assertOk()->assertSee('recementServiceId');
        $this->get(route('staff.services.index'))->assertOk()->assertSee('Staff only');
        $this->delete(route('staff.services.destroy', $this->recement))
            ->assertSessionHasErrors('name');
        $this->assertFalse($this->recement->fresh()->trashed());
    }

    public function test_paid_recement_uses_one_ordinary_receipt_and_appears_in_history(): void
    {
        $visit = $this->saveRow($this->row('500.00', [[
            'amount' => '500.00', 'payment_date' => '2024-01-10', 'method' => 'Cash', 'procedure_index' => 0,
        ]]));

        $this->assertEquals(500, $visit->procedures()->firstOrFail()->price);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('installment_payments', 0);
        $this->assertEquals(0, app(FinancialService::class)->visitBalance($visit));
        $this->assertEquals(500, app(FinancialService::class)->collectedOn('2024-01-10'));
        $this->get(route('staff.visits.show', $visit))->assertOk()->assertSee('Recement');
        $this->get(route('staff.patients.visits', $this->patient))->assertOk()->assertSee('Recement');
        $this->get(route('staff.patients.show', $this->patient))->assertOk()->assertSee('Recement');
    }

    public function test_unpaid_and_partial_recement_keep_the_original_charge(): void
    {
        $visit = $this->saveRow($this->row());
        $this->assertEquals(500, app(FinancialService::class)->visitBalance($visit));
        $this->assertEquals(500, app(FinancialService::class)->summary()['outstanding']);

        $this->post(route('staff.payments.store.cash'), [
            'visit_id' => $visit->id, 'amount' => '200.00',
            'payment_date' => '2024-02-01', 'method' => 'Cash',
        ])->assertRedirect();

        $visit->refresh();
        $this->assertNull($visit->price);
        $this->assertEquals(500, $visit->procedures()->firstOrFail()->price);
        $this->assertEquals(300, app(FinancialService::class)->visitBalance($visit));
        $this->assertEquals(300, app(FinancialService::class)->summary()['outstanding']);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_exceptional_actual_charge_does_not_change_catalog_default(): void
    {
        $visit = $this->saveRow($this->row('650.00'));
        $this->assertEquals(650, $visit->procedures()->firstOrFail()->price);
        $this->assertEquals(500, $this->recement->fresh()->base_price);
        $this->assertEquals(650, app(FinancialService::class)->visitBalance($visit));
    }

    public function test_legacy_visit_form_also_saves_an_adjusted_recement_charge(): void
    {
        $this->get(route('staff.visits.create'))->assertOk()->assertSee('Recement actual charge');
        $this->post(route('staff.visits.store'), [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => '2024-01-10',
            'procedures' => [[
                'service_id' => $this->recement->id,
                'price' => '650.00',
            ]],
        ])->assertRedirect();
        $this->assertEquals(650, Visit::firstOrFail()->procedures()->firstOrFail()->price);
        $this->assertEquals(500, $this->recement->fresh()->base_price);
    }

    public function test_recement_beside_braces_plan_is_ordinary_and_does_not_change_plan(): void
    {
        $braces = Service::create(['name' => 'Braces', 'base_price' => 40000]);
        $row = [
            'visit_date' => '2024-01-10', 'doctor_id' => $this->doctor->id,
            'arrangement' => 'installment',
            'procedures' => [
                ['service_id' => $braces->id, 'price' => '40000.00'],
                ['service_id' => $this->recement->id, 'price' => '500.00'],
            ],
            'payments' => [[
                'amount' => '200.00', 'payment_date' => '2024-01-10',
                'method' => 'Cash', 'procedure_index' => 1,
            ]],
            'plan' => [
                'procedure_index' => 0, 'total_cost' => '40000.00',
                'downpayment' => '8000.00', 'downpayment_date' => '2024-01-10',
                'downpayment_method' => 'Cash', 'start_date' => '2024-01-10',
                'is_open_contract' => false, 'months' => 16, 'payments' => [],
            ],
        ];

        $visit = $this->saveRow($row);
        $plan = InstallmentPlan::firstOrFail();
        $finance = app(FinancialService::class);
        $this->assertEquals(32000, $finance->planBalance($plan));
        $this->assertEquals(300, $finance->ordinaryBalanceOnFinancedVisit($visit, $plan));
        $this->assertEquals(32300, $finance->summary()['outstanding']);
        $this->assertEquals(8200, $finance->collectedOn('2024-01-10'));
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('installment_payments', 1);
        $recementProcedure = $visit->procedures()->where('service_id', $this->recement->id)->firstOrFail();
        $this->assertEquals($recementProcedure->id, Payment::firstOrFail()->visit_procedure_id);

        $this->getJson(route('staff.payments.payable-items', ['patient_id' => $this->patient->id]))
            ->assertOk()->assertJsonFragment(['type' => 'recement', 'id' => $recementProcedure->id, 'balance' => 300]);
        $this->post(route('staff.payments.record'), [
            'patient_id' => $this->patient->id,
            'target_type' => 'recement', 'target_id' => $recementProcedure->id,
            'amount' => '300.00', 'method' => 'Cash', 'payment_date' => '2024-02-10',
            'submission_token' => (string) Str::uuid(),
        ])->assertRedirect();
        $this->assertEquals(32000, $finance->planBalance($plan->fresh()));
        $this->assertEquals(0, $finance->ordinaryBalanceOnFinancedVisit($visit->fresh(), $plan));
        $this->assertEquals(32000, $finance->summary()['outstanding']);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_recement_can_link_prior_braces_plan_but_not_another_patients_plan(): void
    {
        $braces = Service::create(['name' => 'Braces', 'base_price' => 40000]);
        $bracesVisit = Visit::create([
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
            'dentist_name' => $this->doctor->name, 'visit_date' => '2024-01-01',
        ]);
        $bracesVisit->procedures()->create(['service_id' => $braces->id, 'price' => 40000]);
        $plan = InstallmentPlan::create([
            'patient_id' => $this->patient->id, 'visit_id' => $bracesVisit->id,
            'service_id' => $braces->id, 'total_cost' => 40000,
            'downpayment' => 8000, 'balance' => 32000, 'months' => 16,
            'start_date' => '2024-01-01',
        ]);
        $this->getJson(route('staff.records.recement-contexts', ['patient_id' => $this->patient->id]))
            ->assertOk()->assertSee('plan:'.$plan->id);
        $visit = $this->saveRow($this->row('500.00', [], 'plan:'.$plan->id));
        $procedure = $visit->procedures()->firstOrFail();
        $this->assertEquals($plan->id, $procedure->related_installment_plan_id);
        $this->assertEquals(32000, app(FinancialService::class)->planBalance($plan));
        $this->get(route('staff.visits.show', $visit))->assertOk()->assertSee('Related braces plan #'.$plan->id);

        $other = Patient::create(['first_name' => 'Other', 'last_name' => 'Patient']);
        $foreign = InstallmentPlan::create([
            'patient_id' => $other->id, 'service_id' => $braces->id,
            'total_cost' => 40000, 'downpayment' => 0, 'balance' => 40000,
            'months' => 20, 'start_date' => '2024-01-01',
        ]);
        $this->postJson(route('staff.records.review', (string) Str::uuid()), [
            'patient_id' => $this->patient->id, 'mode' => 'past', 'version' => 0,
            'payload' => ['visits' => [$this->row('500.00', [], 'plan:'.$foreign->id)]],
        ])->assertUnprocessable()->assertJsonValidationErrors('visits.0.procedures.0.related_context');
    }

    public function test_recement_can_link_a_braces_visit_without_a_plan(): void
    {
        $braces = Service::create(['name' => 'Orthodontic Braces', 'base_price' => 40000]);
        $bracesVisit = Visit::create([
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
            'dentist_name' => $this->doctor->name, 'visit_date' => '2024-01-01',
        ]);
        $bracesVisit->procedures()->create(['service_id' => $braces->id, 'price' => 40000]);
        $this->getJson(route('staff.records.recement-contexts', ['patient_id' => $this->patient->id]))
            ->assertOk()->assertSee('visit:'.$bracesVisit->id);

        $visit = $this->saveRow($this->row('500.00', [], 'visit:'.$bracesVisit->id));
        $this->assertEquals($bracesVisit->id, $visit->procedures()->firstOrFail()->related_visit_id);
        $this->get(route('staff.visits.show', $visit))->assertOk()->assertSee('Related braces visit #'.$bracesVisit->id);
    }

    public function test_recement_cannot_be_the_financed_procedure_in_past_records(): void
    {
        $row = $this->row();
        $row['arrangement'] = 'installment';
        $row['plan'] = [
            'procedure_index' => 0, 'total_cost' => '500.00',
            'downpayment' => '100.00', 'downpayment_date' => '2024-01-10',
            'downpayment_method' => 'Cash', 'start_date' => '2024-01-10',
            'is_open_contract' => false, 'months' => 2, 'payments' => [],
        ];
        $this->postJson(route('staff.records.review', (string) Str::uuid()), [
            'patient_id' => $this->patient->id, 'mode' => 'past', 'version' => 0,
            'payload' => ['visits' => [$row]],
        ])->assertUnprocessable()->assertJsonValidationErrors('visits.0.plan.procedure_index');
        $this->assertDatabaseCount('installment_plans', 0);
    }

    public function test_legacy_installment_creation_cannot_finance_a_recement_visit(): void
    {
        $visit = $this->saveRow($this->row());
        $this->post(route('staff.payments.store.installment'), [
            'visit_id' => $visit->id,
            'total_cost' => '500.00', 'downpayment' => '0.00',
            'is_open_contract' => 0, 'months' => 2,
            'start_date' => '2024-01-10', 'downpayment_date' => '2024-01-10',
            'downpayment_method' => 'Cash', 'submission_token' => (string) Str::uuid(),
        ])->assertSessionHasErrors('visit_id');
        $this->assertDatabaseCount('installment_plans', 0);
    }
}
