<?php

namespace Tests\Feature;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use App\Services\StaffRecordAssistant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StaffRecordAssistantTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['role' => 'staff', 'is_active' => true]);
    }

    public function test_only_active_staff_can_search_or_ask(): void
    {
        $patient = Patient::create(['first_name' => 'Lina', 'last_name' => 'Santos']);
        $this->postJson(route('staff.assistant.patients'), ['q' => 'Lina'])->assertUnauthorized();
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Balance?'])->assertUnauthorized();

        foreach (['user', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => true]));
            $this->postJson(route('staff.assistant.patients'), ['q' => 'Lina'])->assertForbidden();
            $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Balance?'])->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['role' => 'staff', 'is_active' => false]));
        $this->postJson(route('staff.assistant.patients'), ['q' => 'Lina'])->assertForbidden();
    }

    public function test_staff_layout_renders_the_panel_on_a_staff_page(): void
    {
        $this->actingAs($this->staff());
        $this->get(route('staff.payments.index'))->assertOk()
            ->assertSee('staffRecordAssistantPanel')
            ->assertSeeText('Staff Assistant')
            ->assertSeeText('Read-only')
            ->assertSeeText('Change patient')
            ->assertSee('class="record-assistant__dates" hidden', false)
            ->assertSee('class="record-assistant__form" data-no-loader', false)
            ->assertSee('staff-record-assistant.js');
    }

    public function test_similar_names_are_separate_choices_and_selection_controls_answer(): void
    {
        $one = Patient::create(['first_name' => 'Maria', 'last_name' => 'Santos', 'birthdate' => '1990-01-01']);
        $two = Patient::create(['first_name' => 'Maria', 'last_name' => 'Santos', 'birthdate' => '2010-02-02']);
        $this->actingAs($this->staff());
        $this->getJson(route('staff.assistant.patients', ['q' => 'Maria Santos']))->assertStatus(405);
        $this->postJson(route('staff.assistant.patients'), ['q' => 'Maria Santos'])
            ->assertOk()->assertJsonCount(2, 'patients')
            ->assertJsonFragment(['id' => $one->id, 'name' => 'Maria Santos', 'birthdate' => '1990-01-01'])
            ->assertJsonFragment(['id' => $two->id, 'name' => 'Maria Santos', 'birthdate' => '2010-02-02']);
        $this->postJson(route('staff.assistant.ask'), ['question' => 'What is the balance?'])->assertUnprocessable();
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => 99999, 'question' => 'What is the balance?'])->assertUnprocessable();
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $two->id, 'question' => 'What is the balance?'])
            ->assertOk()->assertJsonPath('links.0.url', route('staff.patients.show', ['patient' => $two, 'tab' => 'tab-payments']));
    }

    public function test_balance_uses_existing_visit_and_plan_math_without_double_charging(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Paying', 'last_name' => 'Patient']);
        $service = Service::create(['name' => 'Cleaning', 'base_price' => 1000]);
        $ordinary = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'status' => 'partial']);
        $ordinary->procedures()->create(['service_id' => $service->id, 'price' => 1000]);
        Payment::create(['visit_id' => $ordinary->id, 'amount' => 400, 'method' => 'Cash', 'payment_date' => '2026-09-01']);
        $financed = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-02', 'status' => 'installment', 'price' => 40000]);
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'visit_id' => $financed->id,
            'service_id' => $service->id, 'total_cost' => 40000, 'downpayment' => 8000,
            'balance' => 32000, 'months' => 16, 'start_date' => '2026-01-01', 'status' => 'Partially Paid']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0,
            'amount' => 8000, 'method' => 'Cash', 'payment_date' => '2026-01-01', 'notes' => 'Downpayment']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 1,
            'amount' => 2000, 'method' => 'Cash', 'payment_date' => '2026-02-01']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'What is the remaining balance?'])
            ->assertOk()->assertJsonPath('message', 'Remaining balance: ₱30,600.00. Ordinary visits: ₱600.00; installment plans: ₱30,000.00.')
            ->assertJsonFragment(['label' => 'Plan #'.$plan->id, 'url' => route('staff.installments.show', $plan)]);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('installment_payments', 2);
    }

    public function test_visit_payment_and_due_answers_link_to_records_and_do_not_invent_receipts(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Ari', 'last_name' => 'Cruz']);
        $service = Service::create(['name' => 'Oral Prophylaxis', 'base_price' => 1000]);
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'status' => 'partial']);
        $visit->procedures()->create(['service_id' => $service->id, 'price' => 1000]);
        $payment = Payment::create(['visit_id' => $visit->id, 'amount' => 250, 'method' => 'Cash', 'payment_date' => '2026-09-01']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'When was the last visit and treatment?'])
            ->assertOk()->assertSee('Oral Prophylaxis')->assertJsonFragment(['label' => 'Visit #'.$visit->id, 'url' => route('staff.visits.show', $visit)]);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Show payment history'])
            ->assertOk()->assertJsonPath('message', "Latest payment receipts:\n2026-09-01 — Ordinary payment ₱250.00 (Cash)")
            ->assertJsonFragment(['label' => 'Receipt #'.$payment->id, 'url' => route('staff.payments.show', $payment)]);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Which installment payments are still due?'])
            ->assertOk()->assertSee('No patient-linked installment plans were found');
    }

    public function test_fixed_plan_due_answer_uses_recorded_months_and_existing_balance(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Dina', 'last_name' => 'Reyes']);
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'total_cost' => 12000,
            'downpayment' => 3000, 'balance' => 9000, 'months' => 3, 'start_date' => '2026-01-01',
            'status' => 'Partially Paid']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0,
            'amount' => 3000, 'method' => 'Cash', 'payment_date' => '2026-01-01', 'notes' => 'Downpayment']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 1,
            'amount' => 3000, 'method' => 'Cash', 'payment_date' => '2026-02-01']);
        $response = $this->postJson(route('staff.assistant.ask'), [
            'patient_id' => $patient->id, 'question' => 'Which installment payments are still due?',
        ])->assertOk()->assertJsonFragment(['label' => 'Plan #'.$plan->id.' schedule', 'url' => route('staff.installments.show', $plan).'#installment-schedule']);
        $this->assertStringContainsString('₱6,000.00 remains', $response->json('message'));
        $this->assertStringContainsString('month 2 (Mar 1, 2026, overdue)', $response->json('message'));
        $this->assertStringNotContainsString('month 1', $response->json('message'));
    }

    public function test_follow_up_questions_keep_the_selected_patient_and_create_no_activity_log(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Follow', 'last_name' => 'Up']);
        $other = Patient::create(['first_name' => 'Follow', 'last_name' => 'Other']);
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-08-05', 'status' => 'partial', 'price' => 1000]);
        Payment::create(['visit_id' => $visit->id, 'amount' => 250, 'method' => 'Cash', 'payment_date' => '2026-08-05']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'What is their balance?'])
            ->assertOk()->assertJsonPath('message', 'Remaining balance: ₱750.00. Ordinary visits: ₱750.00; installment plans: ₱0.00.');
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'And what payments have they made?'])
            ->assertOk()->assertJsonPath('links.0.url', route('staff.patients.show', ['patient' => $patient, 'tab' => 'tab-payments']))
            ->assertJsonFragment(['label' => 'Receipt #1', 'url' => route('staff.payments.show', 1)]);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $other->id, 'question' => 'What is their balance?'])
            ->assertOk()->assertJsonPath('links.0.url', route('staff.patients.show', ['patient' => $other, 'tab' => 'tab-payments']))
            ->assertJsonPath('message', 'No visits or installment plans were found, so I cannot report a balance.');
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_assistant_and_staff_pages_use_computed_plan_balance_and_exact_receipt_link(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Plan', 'last_name' => 'Patient']);
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'total_cost' => 40000,
            'downpayment' => 8000, 'balance' => 39999, 'months' => 12,
            'start_date' => '2026-01-01', 'status' => 'Partially Paid']);
        $receipt = InstallmentPayment::create(['installment_plan_id' => $plan->id,
            'month_number' => 0, 'amount' => 8000, 'method' => 'Cash', 'payment_date' => '2026-01-01', 'notes' => 'Downpayment']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Remaining balance'])
            ->assertOk()->assertJsonPath('message', 'Remaining balance: ₱32,000.00. Ordinary visits: ₱0.00; installment plans: ₱32,000.00.');
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Payment history'])
            ->assertOk()->assertJsonFragment(['label' => 'Installment receipt #'.$receipt->id,
                'url' => route('staff.installments.show', $plan).'#installment-payment-'.$receipt->id]);
        $this->get(route('staff.patients.show', ['patient' => $patient, 'tab' => 'tab-payments']))
            ->assertOk()->assertSee('₱32,000.00')->assertDontSee('₱39,999.00');
        $this->get(route('staff.installments.show', $plan))->assertOk()
            ->assertSee('data-patient-id="'.$patient->id.'"', false)
            ->assertSee('id="installment-payment-'.$receipt->id.'"', false)
            ->assertSee('id="installment-schedule"', false);
    }

    public function test_visit_charge_override_matches_assistant_visit_and_receipt_pages(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Adjusted', 'last_name' => 'Charge']);
        $service = Service::create(['name' => 'Cleaning', 'base_price' => 1000]);
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01',
            'status' => 'partial', 'price' => 700]);
        $visit->procedures()->create(['service_id' => $service->id, 'price' => 1000]);
        $receipt = Payment::create(['visit_id' => $visit->id, 'amount' => 200,
            'method' => 'Cash', 'payment_date' => '2026-09-01']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Balance'])
            ->assertOk()->assertJsonPath('message', 'Remaining balance: ₱500.00. Ordinary visits: ₱500.00; installment plans: ₱0.00.');
        $this->get(route('staff.visits.show', $visit))->assertOk()->assertSee('₱700.00')
            ->assertSee('data-patient-id="'.$patient->id.'"', false);
        $this->get(route('staff.payments.show', $receipt))->assertOk()->assertSee('Visit charge:')->assertSee('₱700.00')
            ->assertSee('data-patient-id="'.$patient->id.'"', false);
    }

    public function test_unavailable_ollama_is_reported_without_guessing(): void
    {
        config()->set('staff_assistant.provider', 'ollama');
        Http::fake(['*' => Http::response(['error' => 'offline'], 503)]);
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Ollama', 'last_name' => 'Test']);
        $this->postJson(route('staff.assistant.ask'), [
            'patient_id' => $patient->id, 'question' => 'Tell me about prior encounters',
        ])->assertOk()->assertJsonPath('message', 'The AI provider is unavailable right now. The standard record questions still work without it.');
    }

    public function test_database_lookup_failure_returns_a_clear_retryable_message(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Maria', 'last_name' => 'Test']);
        $assistant = \Mockery::mock(StaffRecordAssistant::class);
        $assistant->shouldReceive('patients')->once()
            ->andThrow(new QueryException('sqlite', 'select patients', [], new \PDOException('connection lost')));
        $assistant->shouldReceive('answer')->once()
            ->andThrow(new QueryException('sqlite', 'select payments', [], new \PDOException('connection lost')));
        $this->app->instance(StaffRecordAssistant::class, $assistant);
        $this->postJson(route('staff.assistant.patients'), ['q' => 'Maria'])
            ->assertStatus(503)
            ->assertJsonPath('message', 'Patient records are temporarily unavailable. Check the clinic database connection.');
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Balance?'])
            ->assertStatus(503)
            ->assertJsonPath('message', 'Patient records are temporarily unavailable. Check the clinic database connection.');
    }

    public function test_optional_model_only_classifies_question_and_never_receives_records(): void
    {
        config()->set('staff_assistant.provider', 'ollama');
        config()->set('staff_assistant.ollama_url', 'http://localhost:11434');
        Http::fake(['localhost:11434/api/generate' => Http::response(['response' => 'visits'], 200)]);
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Secret', 'last_name' => 'Patient', 'notes' => 'Private medical note']);
        $this->postJson(route('staff.assistant.ask'), [
            'patient_id' => $patient->id, 'question' => 'Tell me about prior encounters',
        ])->assertOk()->assertJsonPath('message', 'No visits were found for this patient.');
        Http::assertSent(function ($request) {
            $body = $request->data();
            return ($body['model'] ?? null) === 'llama3.2:3b'
                && str_contains($body['prompt'] ?? '', 'prior encounters')
                && !str_contains(json_encode($body), 'Private medical note')
                && !str_contains(json_encode($body), 'Secret Patient');
        });
    }

    public function test_identity_requires_selection_and_never_substitutes_a_similar_name(): void
    {
        $this->actingAs($this->staff());
        $first = Patient::create(['first_name' => 'Maria', 'last_name' => 'Santos', 'birthdate' => '1990-01-01']);
        $second = Patient::create(['first_name' => 'Maria', 'last_name' => 'Santos', 'birthdate' => '2010-02-02']);
        $this->postJson(route('staff.assistant.ask'), ['question' => 'Who is this patient?'])->assertUnprocessable();
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $second->id, 'question' => 'Who is this patient?'])
            ->assertOk()->assertSee('patient ID #'.$second->id)->assertSee('2010-02-02')
            ->assertDontSee('1990-01-01')->assertJsonPath('links.0.url', route('staff.patients.show', $second));
        $this->assertNotEquals($first->id, $second->id);
    }

    public function test_mixed_billing_includes_unpaid_ordinary_treatment_without_double_charging_plan(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Mixed', 'last_name' => 'Billing']);
        $oral = Service::create(['name' => 'Oral Prophylaxis', 'base_price' => 1000]);
        $braces = Service::create(['name' => 'Braces', 'base_price' => 40000]);
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'status' => 'installment']);
        $oralRow = $visit->procedures()->create(['service_id' => $oral->id, 'price' => 1000]);
        $visit->procedures()->create(['service_id' => $braces->id, 'price' => 40000]);
        $receipt = Payment::create(['visit_id' => $visit->id, 'visit_procedure_id' => $oralRow->id,
            'amount' => 500, 'method' => 'Cash', 'payment_date' => '2026-09-01']);
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'visit_id' => $visit->id, 'service_id' => $braces->id,
            'total_cost' => 40000, 'downpayment' => 8000, 'balance' => 32000, 'months' => 16, 'start_date' => '2026-09-01']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0,
            'amount' => 8000, 'method' => 'Cash', 'payment_date' => '2026-09-01', 'notes' => 'Downpayment']);

        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'What is the remaining balance?'])
            ->assertOk()->assertJsonPath('message', 'Remaining balance: ₱32,500.00. Ordinary visits: ₱500.00; installment plans: ₱32,000.00.')
            ->assertJsonFragment(['url' => route('staff.visits.show', $visit)])
            ->assertJsonFragment(['url' => route('staff.installments.show', $plan)]);
        $this->postJson(route('staff.assistant.ask'), ['question' => 'Which patients have outstanding balances?'])
            ->assertOk()->assertJsonPath('record_count', 1)->assertJsonPath('total', 32500);
        $this->get(route('staff.patients.show', ['patient' => $patient, 'tab' => 'tab-payments']))
            ->assertOk()->assertSee('₱32,000.00')->assertSee('₱500.00');
        $this->get(route('staff.visits.show', $visit))->assertOk()->assertSee('Oral Prophylaxis')->assertSee('Braces');
        $this->get(route('staff.installments.show', $plan))->assertOk()->assertSee('₱32,000.00');
        $this->get(route('staff.payments.show', $receipt))->assertOk()->assertSee('Oral Prophylaxis');
    }

    public function test_ambiguous_mixed_billing_and_unreceipted_downpayment_are_identified_as_unverified(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Unclear', 'last_name' => 'Record']);
        $service = Service::create(['name' => 'Same Treatment', 'base_price' => 1000]);
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'status' => 'installment']);
        $visit->procedures()->create(['service_id' => $service->id, 'price' => 1000]);
        $visit->procedures()->create(['service_id' => $service->id, 'price' => 1000]);
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'visit_id' => $visit->id, 'service_id' => $service->id,
            'total_cost' => 1000, 'downpayment' => 200, 'balance' => 800, 'months' => 2, 'start_date' => '2026-09-01']);
        $answer = $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Remaining balance?'])
            ->assertOk()->assertSee('Known recorded balance')->assertSee('complete balance is unavailable')
            ->assertSee('not proof that cash was received')
            ->assertJsonFragment(['url' => route('staff.visits.show', $visit)]);
        $this->assertStringNotContainsString('Remaining balance:', $answer->json('message'));
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Show payment history'])
            ->assertOk()->assertSee('No payment receipts were found on patient-linked records');
        $this->postJson(route('staff.assistant.ask'), ['question' => 'Which patients have outstanding balances?'])
            ->assertOk()->assertSee('Incomplete: 1 patient balance(s)')->assertJsonPath('total', 0)
            ->assertJsonFragment(['url' => route('staff.visits.show', $visit)]);
        $this->get(route('staff.installments.show', $plan))->assertOk()->assertSee('₱800.00');
    }

    public function test_unsupported_sensitive_question_is_not_sent_to_optional_provider(): void
    {
        config()->set('staff_assistant.provider', 'ollama');
        Http::fake();
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Private', 'last_name' => 'Patient']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id,
            'question' => 'Does Private Patient have a penicillin allergy?'])
            ->assertOk()->assertSee('I cannot verify that question from the supported records');
        Http::assertNothingSent();
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_provider_rate_limit_has_a_distinct_nonfinancial_message(): void
    {
        config()->set('staff_assistant.provider', 'ollama');
        Http::fake(['*' => Http::response([], 429)]);
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Rate', 'last_name' => 'Limit']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id,
            'question' => 'Tell me about prior encounters'])
            ->assertOk()->assertSee('rate limited')->assertDontSee('Remaining balance');
    }

    public function test_render_localhost_provider_configuration_is_identified_without_network_request(): void
    {
        config()->set('staff_assistant.provider', 'ollama');
        config()->set('staff_assistant.on_render', true);
        config()->set('staff_assistant.ollama_url', 'http://127.0.0.1:11434');
        Http::fake();
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Hosted', 'last_name' => 'Record']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id,
            'question' => 'Tell me about prior encounters'])
            ->assertOk()->assertSee('localhost address on Render');
        Http::assertNothingSent();
    }

    public function test_future_installment_is_not_described_as_due_now(): void
    {
        $this->actingAs($this->staff());
        $this->travelTo(\Carbon\CarbonImmutable::parse('2026-09-29 09:00', 'Asia/Manila')->utc());
        $patient = Patient::create(['first_name' => 'Future', 'last_name' => 'Due']);
        $plan = InstallmentPlan::create(['patient_id' => $patient->id, 'total_cost' => 1000,
            'downpayment' => 0, 'balance' => 1000, 'months' => 1, 'start_date' => '2026-10-01']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id,
            'question' => 'Which installment payments are still due?'])
            ->assertOk()->assertSee('No scheduled month is currently due without a receipt')
            ->assertSee('1 future month')->assertDontSee('overdue')
            ->assertJsonFragment(['url' => route('staff.installments.show', $plan).'#installment-schedule']);
    }

    public function test_visit_linked_plan_without_patient_link_never_produces_a_complete_balance_claim(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Legacy', 'last_name' => 'Link']);
        $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'price' => 1000]);
        InstallmentPlan::create(['patient_id' => null, 'visit_id' => $visit->id,
            'total_cost' => 1000, 'downpayment' => 0, 'balance' => 1000, 'months' => 1, 'start_date' => '2026-09-01']);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id, 'question' => 'Remaining balance?'])
            ->assertOk()->assertSee('Known recorded balance')->assertSee('visit-linked plan(s) do not identify this patient')
            ->assertJsonFragment(['url' => route('staff.visits.show', $visit)]);
        $this->postJson(route('staff.assistant.ask'), ['question' => 'Which patients have outstanding balances?'])
            ->assertOk()->assertSee('mismatched plan links')->assertJsonPath('total', 0)
            ->assertJsonFragment(['url' => route('staff.visits.show', $visit)]);
    }

    public function test_singular_patient_balance_and_visit_count_stay_on_selected_patient(): void
    {
        $this->actingAs($this->staff());
        $patient = Patient::create(['first_name' => 'Selected', 'last_name' => 'Only']);
        $other = Patient::create(['first_name' => 'Other', 'last_name' => 'Person']);
        Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'price' => 500]);
        Visit::create(['patient_id' => $other->id, 'visit_date' => '2026-09-01', 'price' => 1000]);
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id,
            'question' => 'Does this patient have a balance?'])
            ->assertOk()->assertJsonPath('message', 'Remaining balance: ₱500.00. Ordinary visits: ₱500.00; installment plans: ₱0.00.')
            ->assertJsonMissingPath('scope');
        $this->postJson(route('staff.assistant.ask'), ['patient_id' => $patient->id,
            'question' => 'How many visits has this patient had?'])
            ->assertOk()->assertSee('1 recorded visit row')->assertJsonMissingPath('scope');
    }
}
