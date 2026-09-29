<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingRequestDecisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
    }

    public function test_both_untimed_walk_in_kinds_can_be_approved_by_staff_and_admin(): void
    {
        foreach (['staff', 'admin'] as $role) {
            $actor = User::factory()->create(['role' => $role, 'is_active' => true]);
            foreach ([null, 60] as $duration) {
                $appointment = $this->booking($duration, $duration !== null);
                $this->actingAs($actor)->post(route($role.'.approvals.approve', $appointment))->assertSessionHasNoErrors();
                $this->assertSame('walked_in', $appointment->fresh()->status);
                $this->assertNull($appointment->fresh()->appointment_time);
            }
        }
    }

    public function test_scheduled_booking_missing_time_requires_a_slot_and_edit_approve_accepts_one(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $appointment = $this->booking(60, false);
        $this->actingAs($staff)->postJson(route('staff.approvals.approve', $appointment))->assertStatus(422)
            ->assertJsonPath('errors.appointment_time.0', 'Select a time before approving.');
        $this->actingAs($staff)->postJson(route('staff.approvals.approve', $appointment), [
            'appointment_time' => '10:00', 'staff_note' => 'A time was needed for this scheduled request.',
        ])->assertOk();
        $this->assertSame('upcoming', $appointment->fresh()->status);
        $this->assertNotNull($appointment->fresh()->appointment_time);
    }

    public function test_explicit_walk_in_service_with_legacy_duration_is_untimed(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $appointment = $this->booking(60, false);
        $appointment->service->update(['is_walk_in' => true]);
        $this->actingAs($staff)->postJson(route('staff.approvals.approve', $appointment))->assertOk();
        $this->assertSame('walked_in', $appointment->fresh()->status);
        $this->assertNull($appointment->fresh()->appointment_time);
    }

    public function test_staff_created_pending_appointment_is_visible_and_approvable(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $patient = $this->patient('Internal');
        $appointment = $this->booking(60, false, ['patient_id' => $patient->id, 'appointment_time' => '11:00', 'user_id' => null,
            'public_first_name' => null, 'public_last_name' => null, 'public_email' => null]);
        $this->actingAs($staff)->get(route('staff.approvals.index'))->assertOk()->assertSee('Staff-created appointment');
        $this->actingAs($staff)->post(route('staff.approvals.approve', $appointment))->assertSessionHasNoErrors();
        $this->assertSame('upcoming', $appointment->fresh()->status);
    }

    public function test_void_is_audited_and_never_sends_patient_mail_but_decline_does(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $voided = $this->booking(null, false, ['user_id' => $owner->id]);
        $this->actingAs($staff)->postJson(route('staff.approvals.void', $voided), ['reason' => 'Duplicate entry'])->assertOk();
        $this->assertSame('voided', $voided->fresh()->status);
        $this->assertSame($staff->id, $voided->fresh()->voided_by);
        $this->assertNotNull($voided->fresh()->voided_at);
        $this->assertDatabaseHas('activity_logs', ['event' => 'booking.voided', 'target_id' => (string) $voided->id]);
        $this->actingAs($staff)->getJson(route('staff.approvals.widget'))->assertJsonPath('pendingCount', 0);
        Notification::assertNothingSent();
        Mail::assertNothingSent();
        $this->actingAs($staff)->postJson(route('staff.approvals.decline', $voided), ['staff_note' => 'No'])->assertStatus(422);

        $declined = $this->booking(null, false, ['user_id' => $owner->id]);
        $this->actingAs($staff)->postJson(route('staff.approvals.decline', $declined), ['staff_note' => 'Unable to accommodate'])->assertOk();
        $this->assertSame('declined', $declined->fresh()->status);
        Notification::assertSentTo($owner, \App\Notifications\AppointmentDeclined::class, function ($notification) use ($owner) {
            return collect($notification->toMail($owner)->introLines)->contains(fn ($line) => str_contains($line, 'Unable to accommodate'));
        });
    }

    public function test_void_rejects_linked_visit_even_when_soft_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $patient = $this->patient('Linked');
        $appointment = $this->booking(null, false, ['patient_id' => $patient->id]);
        $visit = Visit::create(['patient_id' => $patient->id, 'source_appointment_id' => $appointment->id,
            'visit_date' => today(), 'status' => 'partial']);
        $visit->delete();
        $this->actingAs($admin)->postJson(route('admin.approvals.void', $appointment), ['reason' => 'Duplicate'])->assertStatus(422);
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_shared_contact_requires_explicit_patient_selection(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $first = $this->patient('Shared', 'family@example.test');
        $this->patient('Sibling', 'family@example.test');
        $appointment = $this->booking(null, false, ['public_first_name' => 'Shared', 'public_last_name' => 'Patient',
            'public_email' => 'family@example.test']);
        $this->actingAs($staff)->getJson(route('staff.approvals.patients', $appointment))->assertOk()->assertJsonCount(2, 'items');
        $this->actingAs($staff)->postJson(route('staff.approvals.approve', $appointment))->assertStatus(422)
            ->assertJsonStructure(['errors' => ['patient_id']]);
        $this->assertSame('pending', $appointment->fresh()->status);
        $this->actingAs($staff)->postJson(route('staff.approvals.approve', $appointment), ['patient_id' => (string) $first->id])->assertOk();
        $this->assertSame($first->id, $appointment->fresh()->patient_id);
    }

    public function test_booking_decision_routes_enforce_roles(): void
    {
        $appointment = $this->booking(null, false);
        $this->post(route('staff.approvals.void', $appointment), ['reason' => 'No'])->assertRedirect();
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->actingAs($user)->post(route('staff.approvals.void', $appointment), ['reason' => 'No'])->assertForbidden();
        $this->actingAs($user)->get(route('admin.approvals.patients', $appointment))->assertForbidden();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->actingAs($staff)->post(route('admin.approvals.void', $appointment), ['reason' => 'No'])->assertForbidden();
    }

    public function test_staff_appointment_editor_cannot_bypass_pending_decisions(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $patient = $this->patient('Editor');
        $appointment = $this->booking(60, false, ['patient_id' => $patient->id, 'appointment_time' => '11:00']);
        $this->actingAs($staff)->put(route('staff.appointments.update', $appointment), [
            'patient_id' => $patient->id, 'service_id' => $appointment->service_id,
            'appointment_date' => $appointment->appointment_date,
            'appointment_time' => '11:00', 'dentist_name' => 'Dr. Test', 'status' => 'declined',
        ])->assertSessionHasErrors('status');
        $this->actingAs($staff)->delete(route('staff.appointments.destroy', $appointment))->assertSessionHasErrors('appointment');
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    private function booking(?int $duration, bool $fallback, array $overrides = []): Appointment
    {
        $service = Service::create(['name' => 'Booking '.uniqid(), 'base_price' => 500, 'duration_minutes' => $duration]);
        $appointment = new Appointment();
        $appointment->forceFill(array_merge([
            'service_id' => $service->id, 'patient_id' => null,
            'appointment_date' => today()->addDays(2)->toDateString(), 'appointment_time' => null,
            'status' => 'pending', 'is_walk_in_request' => $fallback,
            'public_first_name' => 'New', 'public_last_name' => 'Patient',
            'public_email' => uniqid().'@example.test',
        ], $overrides));
        $appointment->save();
        return $appointment;
    }

    private function patient(string $first, ?string $email = null): Patient
    {
        return Patient::create(['first_name' => $first, 'last_name' => 'Patient',
            'email' => $email, 'birthdate' => '1990-01-01']);
    }
}
