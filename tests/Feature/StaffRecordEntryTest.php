<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\RecordEntryBatch;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitProcedure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StaffRecordEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Patient $patient;

    private Doctor $doctor;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->patient = Patient::create(['first_name' => 'Paper', 'last_name' => 'Patient']);
        $this->doctor = Doctor::create(['name' => 'Dr. History', 'is_active' => true]);
        $this->service = Service::create(['name' => 'Restoration', 'base_price' => 1800]);
        $this->actingAs($this->staff);
    }

    private function receipt(string $amount = '200.00'): array
    {
        return ['amount' => $amount, 'payment_date' => '2020-02-01', 'method' => 'GCash', 'notes' => 'Paper receipt'];
    }

    private function visit(string $date = '2020-01-01'): array
    {
        return ['visit_date' => $date, 'doctor_id' => $this->doctor->id, 'notes' => 'From paper', 'arrangement' => 'ordinary',
            'procedures' => [['service_id' => $this->service->id, 'price' => '800.00', 'tooth_number' => '11', 'surface' => 'O', 'shade' => 'A1']],
            'payments' => [],
        ];
    }

    private function plan(): array
    {
        return ['procedure_index' => 0, 'total_cost' => '1000.00', 'downpayment' => '200.00', 'start_date' => '2020-01-01',
            'downpayment_date' => '2020-01-01', 'downpayment_method' => 'Card', 'is_open_contract' => false,
            'months' => 4, 'payments' => [
                [...$this->receipt(), 'month_number' => 1],
                [...$this->receipt('100.00'), 'month_number' => 2, 'payment_date' => '2020-03-01'],
            ]];
    }

    private function review(array $visits, string $mode = 'past'): array
    {
        $id = (string) Str::uuid();
        $response = $this->postJson(route('staff.records.review', $id), [
            'patient_id' => $this->patient->id, 'mode' => $mode, 'version' => 0, 'payload' => ['visits' => $visits],
        ])->assertOk();

        return [$id, $response->json('review_hash')];
    }

    private function save(array $review, bool $ack = false)
    {
        return $this->postJson(route('staff.records.store', $review[0]), ['review_hash' => $review[1], 'acknowledge_duplicates' => $ack]);
    }

    public function test_staff_can_save_multiple_visits_and_procedures_at_actual_historical_prices(): void
    {
        $first = $this->visit();
        $first['procedures'][] = ['service_id' => $this->service->id, 'price' => '321.45', 'tooth_number' => '21'];
        $result = $this->save($this->review([$first, $this->visit('2020-04-01')]))->assertOk();
        $result->assertJsonPath('summary.visits', 2)->assertJsonPath('summary.procedures', 3);
        $this->assertDatabaseCount('visits', 2);
        $this->assertEquals([800, 321.45, 800], VisitProcedure::orderBy('id')->pluck('price')->map(fn ($p) => (float) $p)->all());
        $this->assertDatabaseCount('appointments', 0);
        $this->assertEquals(1800, $this->service->fresh()->base_price);
    }

    public function test_multiple_receipts_and_partial_payment_preserve_charge_and_balance(): void
    {
        $row = $this->visit();
        $row['payments'] = [$this->receipt(), $this->receipt('50.25'), ['amount' => '', 'method' => 'Cash']];
        $this->save($this->review([$row]))->assertOk()->assertJsonPath('summary.ordinary_payments', 2);
        $visit = Visit::firstOrFail();
        $this->assertEquals(549.75, $visit->procedures()->sum('price') - $visit->payments()->sum('amount'));
        $this->assertNull($visit->price);
        $this->assertEquals('partial', $visit->status);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_installment_downpayment_counts_once_and_collection_receipts_do_not_create_visits(): void
    {
        $row = $this->visit();
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $this->save($this->review([$row]))->assertOk()->assertJsonPath('summary.installment_payments', 3);
        $plan = InstallmentPlan::firstOrFail();
        $this->assertEquals(500, $plan->balance);
        $this->assertEquals(500, $plan->payments()->sum('amount'));
        $this->assertEquals('Partially Paid', $plan->status);
        $this->assertEquals(1, $plan->payments()->where('month_number', 0)->count());
        $this->assertEquals(0, $plan->payments()->whereNotNull('visit_id')->count());
        $this->assertDatabaseCount('visits', 1);
    }

    public function test_optional_installment_links_can_reference_real_batch_or_existing_patient_visits(): void
    {
        $existing = Visit::create(['patient_id' => $this->patient->id, 'visit_date' => '2019-01-01']);
        $row = $this->visit();
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $row['plan']['payments'][0]['visit_index'] = 1;
        $row['plan']['payments'][1]['visit_id'] = $existing->id;
        $this->save($this->review([$row, $this->visit('2020-02-01')]))->assertOk();
        $this->assertEquals(Visit::latest('id')->first()->id, InstallmentPayment::where('month_number', 1)->first()->visit_id);
        $this->assertEquals($existing->id, InstallmentPayment::where('month_number', 2)->first()->visit_id);
        $this->assertDatabaseCount('visits', 3);
    }

    public function test_open_contract_and_fully_paid_plan_are_supported_without_generated_months(): void
    {
        $row = $this->visit();
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $row['plan']['is_open_contract'] = true;
        $row['plan']['months'] = 0;
        $row['plan']['open_monthly_payment'] = '100.00';
        $row['plan']['payments'] = [[...$this->receipt('800.00'), 'month_number' => 12]];
        $this->save($this->review([$row]))->assertOk();
        $plan = InstallmentPlan::firstOrFail();
        $this->assertTrue($plan->is_open_contract);
        $this->assertEquals(0, $plan->balance);
        $this->assertEquals('Fully Paid', $plan->status);
        $this->assertDatabaseCount('installment_payments', 2);
    }

    public function test_failure_during_second_visit_rolls_back_the_entire_batch(): void
    {
        $review = $this->review([$this->visit(), $this->visit('2020-04-01')]);
        $dispatcher = VisitProcedure::getEventDispatcher();
        VisitProcedure::setEventDispatcher(clone $dispatcher);
        $created = 0;
        VisitProcedure::creating(function () use (&$created) {
            if (++$created === 2) {
                throw new \RuntimeException('Simulated database failure');
            }
        });
        try {
            $this->save($review)->assertStatus(500);
        } finally {
            VisitProcedure::setEventDispatcher($dispatcher);
        }
        $this->assertDatabaseCount('visits', 0);
        $this->assertDatabaseCount('visit_procedures', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertEquals('reviewed', RecordEntryBatch::findOrFail($review[0])->status);
        $this->save($review)->assertOk();
        $this->assertDatabaseCount('visits', 2);
    }

    public function test_same_batch_retry_returns_summary_without_duplicate_records(): void
    {
        $row = $this->visit();
        $row['payments'] = [$this->receipt()];
        $review = $this->review([$row]);
        $first = $this->save($review)->assertOk()->json();
        $this->save($review)->assertOk()->assertExactJson($first);
        $this->assertDatabaseCount('visits', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_duplicate_records_require_acknowledgement_and_never_overwrite(): void
    {
        $this->save($this->review([$this->visit()]))->assertOk();
        $review = $this->review([$this->visit()]);
        $this->assertNotEmpty(RecordEntryBatch::findOrFail($review[0])->warnings);
        $this->save($review)->assertUnprocessable()->assertJsonValidationErrors('duplicates');
        $this->assertDatabaseCount('visits', 1);
        $this->save($review, true)->assertOk();
        $this->assertDatabaseCount('visits', 2);
    }

    public function test_new_duplicate_after_review_requires_another_review(): void
    {
        $first = $this->review([$this->visit()]);
        $second = $this->review([$this->visit()]);
        $this->save($first)->assertOk();
        $this->save($second, true)->assertUnprocessable()->assertJsonValidationErrors('duplicates');
        $this->assertDatabaseCount('visits', 1);
    }

    public function test_nonstaff_cannot_access_entry_or_write_drafts(): void
    {
        foreach (['admin', 'user'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => true]));
            $this->get(route('staff.records.index'))->assertForbidden();
            $this->putJson(route('staff.records.draft', (string) Str::uuid()), [])->assertForbidden();
        }
    }

    public function test_drafts_are_owned_versioned_patient_scoped_and_do_not_create_records(): void
    {
        $id = (string) Str::uuid();
        $body = ['patient_id' => $this->patient->id, 'mode' => 'past', 'version' => 0, 'payload' => ['visits' => [$this->visit()]]];
        $this->putJson(route('staff.records.draft', $id), $body)->assertOk()->assertJsonPath('version', 1);
        $this->assertDatabaseCount('visits', 0);
        $this->putJson(route('staff.records.draft', $id), $body)->assertConflict();
        $body['version'] = 1;
        $body['patient_id'] = Patient::create(['first_name' => 'Other', 'last_name' => 'Patient'])->id;
        $this->putJson(route('staff.records.draft', $id), $body)->assertConflict();
        $this->actingAs(User::factory()->create(['role' => 'staff', 'is_active' => true]));
        $this->deleteJson(route('staff.records.discard', $id))->assertForbidden();
        $this->actingAs($this->staff)->deleteJson(route('staff.records.discard', $id))->assertOk();
        $this->assertEquals('discarded', RecordEntryBatch::findOrFail($id)->status);
        $body['patient_id'] = $this->patient->id;
        $this->putJson(route('staff.records.draft', $id), $body)->assertConflict();
    }

    public function test_invalid_rows_and_overpayments_are_rejected_before_any_clinical_writes(): void
    {
        $row = $this->visit();
        $row['payments'] = [$this->receipt('900.00')];
        $id = (string) Str::uuid();
        $body = ['patient_id' => $this->patient->id, 'mode' => 'past', 'version' => 0, 'payload' => ['visits' => [$row]]];
        $this->postJson(route('staff.records.review', $id), $body)->assertUnprocessable()->assertJsonValidationErrors('visits.0.payments');
        $row = $this->visit();
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $row['plan']['payments'][0]['month_number'] = 0;
        $body['payload']['visits'] = [$row];
        $this->postJson(route('staff.records.review', $id), $body)->assertUnprocessable()->assertJsonValidationErrors('visits.0.plan.payments.0.month_number');
        $this->assertDatabaseCount('visits', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_zero_combined_balance_cannot_hide_an_overpaid_installment_plan(): void
    {
        $row = $this->visit();
        $row['procedures'][0]['price'] = '1000.00';
        $row['procedures'][] = ['service_id' => $this->service->id, 'price' => '100.00'];
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $row['plan']['payments'] = [[...$this->receipt('900.00'), 'month_number' => 1]];
        // Agreed total and received total are both 1,100, but the plan is 100 over and the ordinary treatment is 100 unpaid.
        $response = $this->postJson(route('staff.records.review', (string) Str::uuid()), [
            'patient_id' => $this->patient->id, 'mode' => 'past', 'version' => 0,
            'payload' => ['visits' => [$row]],
        ])->assertUnprocessable()->assertJsonValidationErrors('visits.0.plan.payments');
        $message = $response->json('errors.visits.0.plan.payments.0');
        $this->assertStringContainsString('₱100.00 over', $message);
        $this->assertStringContainsString('ordinary receipt', $message);
        $this->assertDatabaseCount('visits', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('installment_payments', 0);
    }

    public function test_normal_patient_add_visit_supports_no_payment_payment_or_plan_and_profile_return(): void
    {
        foreach (['none', 'payment', 'plan'] as $offset => $kind) {
            $row = $this->visit('2020-01-0'.($offset + 1));
            if ($kind === 'payment') {
                $row['payments'] = [$this->receipt()];
            }
            if ($kind === 'plan') {
                $row['arrangement'] = 'installment';
                $row['plan'] = $this->plan();
            }
            $result = $this->save($this->review([$row], 'visit'))->assertOk();
            $this->assertStringContainsString('tab='.($kind === 'none' ? 'tab-visits' : 'tab-payments'), $result->json('redirect'));
        }
        $this->assertDatabaseCount('visits', 3);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('installment_plans', 1);
    }

    public function test_pages_render_and_existing_visit_payment_routes_continue_to_work(): void
    {
        foreach (['staff.records.index', 'staff.visits.index', 'staff.visits.create', 'staff.payments.index', 'staff.payments.create.cash', 'staff.payments.create.installment'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('staff.records.index', ['patient_id' => $this->patient->id]))->assertOk()->assertSee('Review Records');
        $this->get(route('staff.patients.show', $this->patient))->assertOk()->assertSee('Enter Past Records');
        $this->post(route('staff.visits.store'), [
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'visit_date' => '2020-01-01', 'procedures' => [['service_id' => $this->service->id]],
        ])->assertRedirect();
        $this->post(route('staff.payments.store.cash'), ['visit_id' => Visit::firstOrFail()->id, ...$this->receipt('1800.00')])->assertRedirect();
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_legacy_visit_edit_keeps_existing_procedure_ids_and_historical_prices(): void
    {
        $this->save($this->review([$this->visit()]))->assertOk();
        $visit = Visit::firstOrFail();
        $procedure = $visit->procedures()->firstOrFail();
        $this->put(route('staff.visits.update', $visit), [
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'visit_date' => '2020-01-01', 'notes' => 'Corrected note',
            'procedures' => [['id' => $procedure->id, 'service_id' => $this->service->id, 'tooth_number' => '11', 'notes' => 'Updated procedure note']],
        ])->assertRedirect();
        $this->assertDatabaseHas('visit_procedures', ['id' => $procedure->id, 'price' => 800, 'notes' => 'Updated procedure note']);
        $this->assertDatabaseCount('visit_procedures', 1);
    }

    public function test_installment_get_pages_do_not_write_payments_or_balances(): void
    {
        $row = $this->visit();
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $this->save($this->review([$row]))->assertOk();
        $plan = InstallmentPlan::firstOrFail();
        $plan->payments()->where('month_number', 0)->forceDelete(); // Simulate legacy downpayment field without a receipt.
        $before = $plan->fresh()->getAttributes();
        foreach (['staff.installments.show', 'staff.installments.edit', 'staff.installments.pay'] as $route) {
            $this->get(route($route, $plan))->assertOk();
        }
        $this->get(route('staff.installments.payments.edit', [$plan, $plan->payments()->firstOrFail()]))->assertOk();
        $this->assertDatabaseCount('installment_payments', 2);
        $this->assertSame($before, $plan->fresh()->getAttributes());
    }

    public function test_installment_receipt_edit_does_not_rewrite_linked_treatment_visit(): void
    {
        $row = $this->visit();
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $row['plan']['payments'][0]['visit_index'] = 0;
        $this->save($this->review([$row]))->assertOk();
        $visit = Visit::firstOrFail();
        $before = $visit->getAttributes();
        $plan = InstallmentPlan::firstOrFail();
        $payment = $plan->payments()->where('month_number', 1)->firstOrFail();
        $this->put(route('staff.installments.payments.update', [$plan, $payment]), [
            'amount' => '250.00', 'method' => 'Bank Transfer', 'payment_date' => '2020-03-20', 'notes' => 'Corrected receipt',
        ])->assertRedirect();
        $this->assertSame($before, $visit->fresh()->getAttributes());
        $this->assertEquals(450, $plan->fresh()->balance);
    }

    public function test_contextual_payment_preserves_historical_custom_charge_and_returns_to_profile(): void
    {
        $this->service->update(['allow_custom_price' => true]);
        $this->save($this->review([$this->visit()]))->assertOk();
        $return = route('staff.patients.show', ['patient' => $this->patient->id, 'tab' => 'tab-payments']);
        $this->post(route('staff.payments.store.cash'), [
            'visit_id' => Visit::firstOrFail()->id, ...$this->receipt(), 'preserve_charge' => true, 'return' => $return,
        ])->assertRedirect($return);
        $visit = Visit::firstOrFail();
        $this->assertNull($visit->price);
        $this->assertEquals(600, $visit->procedures()->sum('price') - $visit->payments()->sum('amount'));
        $this->assertEquals('Paper receipt', Payment::firstOrFail()->notes);
    }

    public function test_contextual_forms_limit_the_patient_and_keep_return_destination(): void
    {
        $other = Patient::create(['first_name' => 'Different', 'last_name' => 'Person']);
        Visit::create(['patient_id' => $other->id, 'visit_date' => '2020-01-01']);
        $return = route('staff.patients.show', $this->patient);
        foreach (['staff.payments.create.cash', 'staff.payments.create.installment', 'staff.appointments.create'] as $name) {
            $this->get(route($name, ['patient_id' => $this->patient->id, 'return' => $return]))->assertOk()->assertSee($return);
        }
        $this->get(route('staff.appointments.create', ['patient_id' => $this->patient->id]))->assertDontSee('Different');
        $this->get(route('staff.visits.create', ['patient_id' => $this->patient->id]))->assertRedirect(route('staff.records.index', ['patient_id' => $this->patient->id, 'mode' => 'visit']));
    }

    public function test_duplicate_months_foreign_visit_links_and_stale_reviews_are_rejected(): void
    {
        $other = Patient::create(['first_name' => 'Other', 'last_name' => 'Patient']);
        $foreign = Visit::create(['patient_id' => $other->id, 'visit_date' => '2020-01-01']);
        $row = $this->visit();
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $row['plan']['payments'][0]['visit_id'] = $foreign->id;
        $body = ['patient_id' => $this->patient->id, 'mode' => 'past', 'version' => 0, 'payload' => ['visits' => [$row]]];
        $this->postJson(route('staff.records.review', (string) Str::uuid()), $body)->assertUnprocessable()->assertJsonValidationErrors('visits.0.plan.payments.0.visit_id');
        unset($row['plan']['payments'][0]['visit_id']);
        $row['plan']['payments'][1]['month_number'] = 1;
        $body['payload']['visits'] = [$row];
        $this->postJson(route('staff.records.review', (string) Str::uuid()), $body)->assertUnprocessable()->assertJsonValidationErrors('visits.0.plan.payments.1.month_number');
        $review = $this->review([$this->visit()]);
        $body['payload']['visits'] = [$this->visit()];
        $body['version'] = 1;
        $this->putJson(route('staff.records.draft', $review[0]), $body)->assertOk();
        $this->save($review)->assertConflict();
        $this->assertDatabaseCount('visits', 1);
    }

    public function test_entry_templates_can_be_exported_for_optional_browser_smoke_test(): void
    {
        $this->patient->update(['contact_number' => '09171234567']);
        $selector = $this->get(route('staff.records.index'))
            ->assertOk()
            ->assertSee('role="combobox"', false)
            ->assertSee('09171234567')
            ->assertDontSee('id="re-patient-select"', false);

        if (getenv('KT_BROWSER_FIXTURES')) {
            Service::firstOrCreate(['name' => 'Oral Prophylaxis'], ['base_price' => 1000]);
            Service::firstOrCreate(['name' => 'Braces'], ['base_price' => 48000]);
            $directory = base_path('tests/Browser/.fixtures');
            if (! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
            file_put_contents($directory.'/select.html', $selector->getContent());
        }
        foreach (['past', 'visit'] as $mode) {
            $response = $this->get(route('staff.records.index', ['patient_id' => $this->patient->id, 'mode' => $mode]))->assertOk();
            if (getenv('KT_BROWSER_FIXTURES')) {
                file_put_contents($directory.'/'.$mode.'.html', $response->getContent());
            }
        }
    }

    public function test_unpaid_installment_plan_needs_no_receipt_metadata_and_creates_no_payments(): void
    {
        $row = $this->visit();
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $row['plan']['downpayment'] = '0';
        $row['plan']['payments'] = [];
        unset($row['plan']['downpayment_date'], $row['plan']['downpayment_method']);
        $this->save($this->review([$row]))->assertOk();
        $this->assertDatabaseCount('installment_payments', 0);
        $this->assertEquals(1000, InstallmentPlan::firstOrFail()->balance);
    }

    public function test_mixed_billing_allocates_ordinary_receipt_to_oral_prophylaxis_and_plan_to_braces(): void
    {
        $oral = Service::create(['name' => 'Oral Prophylaxis', 'base_price' => 1000]);
        $braces = Service::create(['name' => 'Braces', 'base_price' => 48000]);
        $row = $this->visit();
        $row['procedures'] = [
            ['service_id' => $oral->id, 'price' => '1000.00'],
            ['service_id' => $braces->id, 'price' => '48000.00'],
        ];
        $row['payments'] = [[...$this->receipt('1000.00'), 'procedure_index' => 0]];
        $row['arrangement'] = 'installment';
        $row['plan'] = [
            'procedure_index' => 1,
            'total_cost' => '48000.00',
            'downpayment' => '8000.00',
            'start_date' => '2020-01-01',
            'downpayment_date' => '2020-01-01',
            'downpayment_method' => 'Cash',
            'is_open_contract' => false,
            'months' => 20,
            'payments' => [],
        ];

        $this->save($this->review([$row]))->assertOk()
            ->assertJsonPath('summary.ordinary_payments', 1)
            ->assertJsonPath('summary.installment_plans', 1)
            ->assertJsonPath('summary.installment_payments', 1);

        $procedures = VisitProcedure::with('service')->orderBy('id')->get();
        $payment = Payment::firstOrFail();
        $plan = InstallmentPlan::firstOrFail();
        $this->assertEquals($procedures[0]->id, $payment->visit_procedure_id);
        $this->assertEquals('Oral Prophylaxis', $payment->procedure->service->name);
        $this->assertEquals($braces->id, $plan->service_id);
        $this->assertEquals(40000, $plan->balance);
        $this->get(route('staff.payments.show', $payment))
            ->assertOk()
            ->assertSee('Payment For')
            ->assertSee('Oral Prophylaxis');

        $edit = [
            'visit_id' => $payment->visit_id,
            'amount' => '1100.00',
            'method' => 'Cash',
            'payment_date' => '2020-02-01',
        ];
        $this->put(route('staff.payments.update', $payment), $edit)->assertSessionHasErrors('amount');
        $this->assertEquals(1000, (float) $payment->fresh()->amount);
        $edit['amount'] = '900.00';
        $this->put(route('staff.payments.update', $payment), $edit)->assertRedirect();
        $this->assertEquals($procedures[0]->id, $payment->fresh()->visit_procedure_id);
        $this->assertEquals('installment', $payment->visit->fresh()->status);
    }

    public function test_mixed_billing_rejects_unallocated_or_financed_treatment_receipts(): void
    {
        $row = $this->visit();
        $row['procedures'][] = ['service_id' => $this->service->id, 'price' => '1000.00'];
        $row['arrangement'] = 'installment';
        $row['plan'] = $this->plan();
        $row['plan']['procedure_index'] = 1;
        $row['payments'] = [$this->receipt('100.00')];
        $body = ['patient_id' => $this->patient->id, 'mode' => 'past', 'version' => 0, 'payload' => ['visits' => [$row]]];
        $this->postJson(route('staff.records.review', (string) Str::uuid()), $body)
            ->assertUnprocessable()->assertJsonValidationErrors('visits.0.payments.0.procedure_index');

        $row['payments'][0]['procedure_index'] = 1;
        $body['payload']['visits'] = [$row];
        $this->postJson(route('staff.records.review', (string) Str::uuid()), $body)
            ->assertUnprocessable()->assertJsonValidationErrors('visits.0.payments.0.procedure_index');

        $row['payments'][0]['procedure_index'] = 0;
        $row['payments'][0]['amount'] = '900.00';
        $body['payload']['visits'] = [$row];
        $this->postJson(route('staff.records.review', (string) Str::uuid()), $body)
            ->assertUnprocessable()->assertJsonValidationErrors('visits.0.payments');
        $this->assertDatabaseCount('visits', 0);
    }
}
