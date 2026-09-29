<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Doctor;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Patient $patient;
    private Service $service;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Manila']);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-26 10:00:00', 'Asia/Manila'));
        $this->staff = User::factory()->create(['role' => 'staff', 'is_active' => true, 'name' => 'Clinic Staff']);
        $this->patient = Patient::create(['first_name' => 'Ana', 'last_name' => 'Santos']);
        $this->service = Service::create(['name' => 'Dental Cleaning', 'base_price' => 1000]);
        $this->doctor = Doctor::create(['name' => 'Dr. Tell', 'is_active' => true]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function appointment(array $overrides = []): Appointment
    {
        return Appointment::create(array_merge([
            'patient_id' => $this->patient->id,
            'service_id' => $this->service->id,
            'doctor_id' => $this->doctor->id,
            'dentist_name' => $this->doctor->name,
            'appointment_date' => '2026-09-26',
            'appointment_time' => '10:00:00',
            'status' => 'scheduled',
        ], $overrides));
    }

    private function visit(array $overrides = [], bool $withCharge = false): Visit
    {
        $visit = Visit::create(array_merge([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'dentist_name' => $this->doctor->name,
            'visit_date' => '2026-09-26',
            'status' => 'partial',
        ], $overrides));
        if ($withCharge) {
            $visit->procedures()->create(['service_id' => $this->service->id, 'price' => 1000]);
        }
        return $visit;
    }

    private function plan(Visit $visit, array $overrides = []): InstallmentPlan
    {
        return InstallmentPlan::create(array_merge([
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'service_id' => $this->service->id,
            'total_cost' => 40000,
            'downpayment' => 8000,
            'balance' => 32000,
            'months' => 16,
            'start_date' => '2026-09-01',
            'status' => 'Partially Paid',
            'is_open_contract' => false,
        ], $overrides));
    }

    private function dashboard()
    {
        return $this->actingAs($this->staff)->get(route('staff.dashboard'));
    }

    public function test_staff_dashboard_is_accessible_to_staff(): void
    {
        $this->dashboard()->assertOk()->assertSeeText('STAFF DASHBOARD')->assertSeeText('Good morning, Clinic Staff');
    }

    public function test_non_staff_users_cannot_access_dashboard(): void
    {
        $patientUser = User::factory()->create(['role' => 'patient', 'is_active' => true]);
        $this->actingAs($patientUser)->get(route('staff.dashboard'))->assertForbidden();
    }

    public function test_today_appointment_count_uses_application_timezone(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-25 16:30:00', 'UTC'));
        $this->appointment(['appointment_date' => '2026-09-26']);
        $this->appointment(['appointment_date' => '2026-09-25']);
        $this->dashboard()->assertSee('data-metric="today-appointments">1', false);
    }

    public function test_cancelled_and_declined_appointments_are_excluded(): void
    {
        $this->appointment(['status' => 'scheduled']);
        $this->appointment(['status' => 'cancelled']);
        $this->appointment(['status' => 'declined']);
        $this->dashboard()->assertSee('data-metric="today-appointments">1', false);
    }

    public function test_scheduled_and_upcoming_appointments_appear(): void
    {
        $this->patient->update(['first_name' => 'Scheduled']);
        $second = Patient::create(['first_name' => 'Upcoming', 'last_name' => 'Patient']);
        $this->appointment(['status' => 'scheduled']);
        $this->appointment(['patient_id' => $second->id, 'status' => 'upcoming', 'appointment_time' => '11:00:00']);
        $this->dashboard()->assertSeeText('Scheduled Santos')->assertSeeText('Upcoming Patient')
            ->assertSee('data-metric="today-appointments">2', false);
    }

    public function test_visits_recorded_uses_visit_date(): void
    {
        $this->visit(['visit_date' => '2026-09-26']);
        $this->visit(['visit_date' => '2026-09-25']);
        $baseVisit = $this->visit(['visit_date' => '2026-09-01']);
        $plan = $this->plan($baseVisit);
        $collectionOnlyVisit = $this->visit(['visit_date' => '2026-09-26', 'status' => 'completed']);
        $collectionOnlyVisit->procedures()->create(['service_id' => $this->service->id, 'price' => 0]);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'visit_id' => $collectionOnlyVisit->id,
            'month_number' => 1, 'amount' => 1000, 'method' => 'Cash', 'payment_date' => '2026-09-26']);
        $this->dashboard()->assertSee('data-metric="today-visits">1', false);
    }

    public function test_historical_visit_created_today_is_not_counted_as_today(): void
    {
        $visit = $this->visit(['visit_date' => '2025-01-10']);
        $this->assertSame('2026-09-26', $visit->created_at->toDateString());
        $this->dashboard()->assertSee('data-metric="today-visits">0', false);
    }

    public function test_collected_today_includes_ordinary_payments(): void
    {
        $visit = $this->visit();
        Payment::create(['visit_id' => $visit->id, 'amount' => 1250, 'method' => 'Cash', 'payment_date' => '2026-09-26']);
        $this->dashboard()->assertSee('data-metric="collected-today">₱1,250.00', false);
    }

    public function test_collected_today_includes_installment_payments(): void
    {
        $plan = $this->plan($this->visit());
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'visit_id' => $plan->visit_id, 'month_number' => 1,
            'amount' => 2000, 'method' => 'GCash', 'payment_date' => '2026-09-26']);
        $this->dashboard()->assertSee('data-metric="collected-today">₱2,000.00', false);
    }

    public function test_installment_downpayment_receipt_is_counted_once(): void
    {
        $plan = $this->plan($this->visit());
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'visit_id' => $plan->visit_id, 'month_number' => 0,
            'amount' => 8000, 'method' => 'Cash', 'payment_date' => '2026-09-26', 'notes' => 'Downpayment']);
        $this->dashboard()->assertSee('data-metric="collected-today">₱8,000.00', false);
    }

    public function test_soft_deleted_payments_are_excluded(): void
    {
        $visit = $this->visit();
        $ordinary = Payment::create(['visit_id' => $visit->id, 'amount' => 500, 'method' => 'Cash', 'payment_date' => '2026-09-26']);
        $ordinary->delete();
        $plan = $this->plan($visit);
        $installment = InstallmentPayment::create(['installment_plan_id' => $plan->id, 'visit_id' => $visit->id, 'month_number' => 1,
            'amount' => 700, 'method' => 'Cash', 'payment_date' => '2026-09-26']);
        $installment->delete();
        $this->dashboard()->assertSee('data-metric="collected-today">₱0.00', false);
    }

    public function test_upcoming_list_excludes_today_and_invalid_statuses(): void
    {
        $this->patient->update(['first_name' => 'Today']);
        $valid = Patient::create(['first_name' => 'FutureValid', 'last_name' => 'Patient']);
        $invalid = Patient::create(['first_name' => 'FutureCancelled', 'last_name' => 'Patient']);
        $this->appointment();
        $this->appointment(['patient_id' => $valid->id, 'appointment_date' => '2026-09-27', 'status' => 'upcoming']);
        $this->appointment(['patient_id' => $invalid->id, 'appointment_date' => '2026-09-27', 'status' => 'cancelled']);
        $response = $this->dashboard();
        $upcomingHtml = str($response->getContent())->after('id="upcomingTitle"')->before('id="calendarTitle"')->toString();
        $this->assertStringContainsString('FutureValid Patient', $upcomingHtml);
        $this->assertStringNotContainsString('Today Santos', $upcomingHtml);
        $this->assertStringNotContainsString('FutureCancelled Patient', $upcomingHtml);
    }

    public function test_overdue_pending_booking_request_is_flagged(): void
    {
        $this->appointment(['status' => 'pending', 'appointment_date' => '2026-03-01']);
        $this->dashboard()->assertSeeText('Overdue')->assertSeeText('Mar 1, 2026');
    }

    public function test_needs_attention_matches_displayed_category_counts(): void
    {
        $this->appointment(['status' => 'pending']);
        $this->appointment(['status' => 'upcoming', 'appointment_date' => '2026-09-28', 'appointment_time' => '13:30:00']);
        $this->visit([], true);
        ContactMessage::create(['name' => 'Sender', 'email' => 'sender@example.test', 'message' => 'Please call me.']);
        $response = $this->dashboard()
            ->assertSee('data-metric="needs-attention">3', false)
            ->assertSeeText('1 request · 1 patient balance to review · 1 message');

        if (getenv('KT_DASHBOARD_BROWSER_FIXTURE')) {
            $directory = base_path('tests/Browser/.fixtures');
            if (!is_dir($directory)) mkdir($directory, 0777, true);
            file_put_contents($directory . '/staff-dashboard.html', $response->getContent());
        }
    }

    public function test_dashboard_and_assistant_share_mixed_billing_balance_and_flag_incomplete_records(): void
    {
        $ordinaryService = Service::create(['name' => 'Cleaning', 'base_price' => 1000]);
        $mixed = $this->visit();
        $mixed->procedures()->create(['service_id' => $this->service->id, 'price' => 40000]);
        $cleaning = $mixed->procedures()->create(['service_id' => $ordinaryService->id, 'price' => 1000]);
        $plan = $this->plan($mixed);
        Payment::create(['visit_id' => $mixed->id, 'visit_procedure_id' => $cleaning->id,
            'amount' => 400, 'method' => 'Cash', 'payment_date' => '2026-09-26']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 0,
            'amount' => 8000, 'method' => 'Cash', 'payment_date' => '2026-09-26', 'notes' => 'Downpayment']);
        InstallmentPayment::create(['installment_plan_id' => $plan->id, 'month_number' => 1,
            'amount' => 2000, 'method' => 'Cash', 'payment_date' => '2026-09-26']);

        $unclearPatient = Patient::create(['first_name' => 'Needs', 'last_name' => 'Review']);
        $unclear = Visit::create(['patient_id' => $unclearPatient->id, 'visit_date' => '2026-09-26']);
        $unclear->procedures()->create(['service_id' => $this->service->id, 'price' => 40000]);
        $unclear->procedures()->create(['service_id' => $ordinaryService->id, 'price' => 1000]);
        InstallmentPlan::create(['patient_id' => $unclearPatient->id, 'visit_id' => $unclear->id,
            'total_cost' => 40000, 'downpayment' => 0, 'balance' => 40000,
            'months' => 16, 'start_date' => '2026-09-01']);

        $this->dashboard()->assertOk()
            ->assertViewHas('balanceCount', 2)
            ->assertViewHas('balanceKnownCount', 1)
            ->assertViewHas('balanceIncompleteCount', 1)
            ->assertViewHas('balanceKnownTotal', 30600.0)
            ->assertSeeText('Incomplete')
            ->assertSee(route('staff.visits.show', $unclear), false);
        $this->postJson(route('staff.assistant.ask'), ['question' => 'Which patients have outstanding balances?'])
            ->assertOk()->assertJsonPath('record_count', 1)->assertJsonPath('total', 30600)
            ->assertSee('Incomplete: 1 patient balance(s)')
            ->assertJsonFragment(['url' => route('staff.visits.show', $unclear)]);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('installment_payments', 2);
    }

    public function test_dashboard_get_does_not_create_or_modify_records(): void
    {
        $appointment = $this->appointment(['status' => 'pending']);
        $message = ContactMessage::create(['name' => 'Unread', 'email' => 'unread@example.test', 'message' => 'Question']);
        $before = [$appointment->fresh()->toArray(), $message->fresh()->toArray()];
        $counts = [Appointment::count(), ContactMessage::count(), Visit::count(), Payment::count(), InstallmentPayment::count()];
        $this->dashboard()->assertOk();
        $this->assertSame($counts, [Appointment::count(), ContactMessage::count(), Visit::count(), Payment::count(), InstallmentPayment::count()]);
        $this->assertSame($before, [$appointment->fresh()->toArray(), $message->fresh()->toArray()]);
    }

    public function test_calendar_endpoint_returns_active_events_and_keeps_walk_ins_untimed(): void
    {
        $walkIn = $this->appointment(['appointment_time' => null, 'status' => 'walked_in']);
        $this->appointment(['appointment_time' => '09:00:00', 'status' => 'cancelled']);
        $response = $this->actingAs($this->staff)->getJson(route('staff.dashboard.calendar.events', [
            'start' => '2026-09-25T00:00:00+08:00',
            'end' => '2026-09-28T00:00:00+08:00',
        ]))->assertOk()->assertJsonCount(1);
        $response->assertJsonPath('0.id', (string) $walkIn->id)
            ->assertJsonPath('0.allDay', true)
            ->assertJsonPath('0.start', '2026-09-26');
        $this->assertArrayNotHasKey('end', $response->json('0'));
    }
}
