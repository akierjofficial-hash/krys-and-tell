<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_bookings_do_not_block_slot_availability(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Comprehensive Checkup',
            'base_price' => 1200,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $patient = $this->makePatient();
        $date = now()->addDays(5)->toDateString();

        // Two pending rows at 14:00 should NOT block this slot.
        $this->makeAppointment($patient->id, $service->id, $date, '14:00:00', 'pending');
        $this->makeAppointment($patient->id, $service->id, $date, '14:00:00', 'pending');

        // Two approved/upcoming rows at 15:00 should block this slot.
        $this->makeAppointment($patient->id, $service->id, $date, '15:00:00', 'upcoming');
        $this->makeAppointment($patient->id, $service->id, $date, '15:00:00', 'upcoming');

        $response = $this->actingAs($user)->getJson(route('public.booking.slots', [
            'service' => $service->id,
            'date' => $date,
        ]));

        $response->assertOk();
        $slots = $response->json('slots');

        $this->assertIsArray($slots);
        $this->assertContains('14:00', $slots, 'Pending bookings should not lock slots.');
        $this->assertNotContains('15:00', $slots, 'Approved/upcoming bookings should still lock slots.');
    }

    public function test_slots_are_computed_per_selected_dentist_schedule(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Root Canal',
            'base_price' => 2500,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $patient = $this->makePatient();
        $date = now()->addDays(5)->toDateString();

        $doctorA = Doctor::create([
            'name' => 'Doctor A',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6],
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $doctorB = Doctor::create([
            'name' => 'Doctor B',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6],
            'work_start_time' => '13:00:00',
            'work_end_time' => '17:00:00',
        ]);

        // Doctor A already has 13:00 occupied.
        $this->makeAppointment($patient->id, $service->id, $date, '13:00:00', 'upcoming', $doctorA->id);

        $slotsForA = $this->actingAs($user)
            ->getJson(route('public.booking.slots', [
                'service' => $service->id,
                'date' => $date,
                'doctor_id' => $doctorA->id,
            ]))
            ->assertOk()
            ->json('slots');

        $slotsForB = $this->actingAs($user)
            ->getJson(route('public.booking.slots', [
                'service' => $service->id,
                'date' => $date,
                'doctor_id' => $doctorB->id,
            ]))
            ->assertOk()
            ->json('slots');

        $this->assertNotContains('13:00', $slotsForA, 'Selected dentist should not show own occupied hour.');
        $this->assertContains('13:00', $slotsForB, 'Another dentist can still show the same hour when free.');
        $this->assertNotContains('09:00', $slotsForB, 'Dentist B starts at 1:00 PM, so morning slots must be hidden.');
    }

    public function test_repeat_booking_updates_existing_pending_request_instead_of_creating_new_row(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Dental Cleaning',
            'base_price' => 900,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $patient = $this->makePatient();
        $date = now()->addDays(4)->toDateString();

        $existing = $this->makeAppointment($patient->id, $service->id, $date, '10:00:00', 'pending');
        if (Schema::hasColumn('appointments', 'user_id')) {
            $existing->user_id = $user->id;
        }
        if (Schema::hasColumn('appointments', 'public_email')) {
            $existing->public_email = $user->email;
        }
        if (Schema::hasColumn('appointments', 'public_name')) {
            $existing->public_name = $user->name;
        }
        $existing->save();

        $response = $this->actingAs($user)->post(route('public.booking.store', $service->id), [
            'date' => $date,
            'time' => '11:00',
            'full_name' => 'Updated User Name',
            'contact' => '09123456789',
            'address' => 'Updated Address',
            'birthdate' => '1995-01-01',
            'message' => 'Please update my request.',
        ]);

        $response->assertRedirect(route('public.booking.create', $service->id));

        $pendingQuery = Appointment::query()
            ->where('service_id', $service->id)
            ->where('status', 'pending');

        if (Schema::hasColumn('appointments', 'user_id')) {
            $pendingQuery->where('user_id', $user->id);
        } elseif (Schema::hasColumn('appointments', 'public_email')) {
            $pendingQuery->where('public_email', $user->email);
        }

        $pending = $pendingQuery->get();

        $this->assertCount(1, $pending, 'Repeat booking should update existing pending request.');
        $this->assertSame((int) $existing->id, (int) $pending->first()->id, 'Existing pending row should be reused.');

        $updatedTime = Carbon::parse($pending->first()->appointment_time)->format('H:i');
        $this->assertSame('11:00', $updatedTime);
    }

    public function test_user_can_open_edit_page_for_own_pending_booking(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Tooth Filling',
            'base_price' => 1500,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $patient = $this->makePatient();
        $date = now()->addDays(3)->toDateString();

        $appt = $this->makeAppointment($patient->id, $service->id, $date, '09:00:00', 'pending');
        if (Schema::hasColumn('appointments', 'user_id')) {
            $appt->user_id = $user->id;
        }
        if (Schema::hasColumn('appointments', 'public_email')) {
            $appt->public_email = $user->email;
        }
        $appt->save();

        $this->actingAs($user)
            ->get(route('public.booking.edit', $appt->id))
            ->assertOk();
    }

    public function test_user_can_update_existing_walk_in_request_without_time(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Consultation',
            'base_price' => 700,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $doctor = Doctor::create([
            'name' => 'Dr. Pending Walkin',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6, 7],
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $patient = $this->makePatient();
        $date = now()->toDateString();

        $appt = Appointment::create([
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'appointment_time' => null,
            'duration_minutes' => 60,
            'status' => 'pending',
            'is_walk_in_request' => true,
            'public_email' => $user->email,
            'public_message' => 'Initial walk-in request.',
        ]);

        if (Schema::hasColumn('appointments', 'user_id')) {
            $appt->user_id = $user->id;
            $appt->save();
        }

        $response = $this->actingAs($user)->put(route('public.booking.update', $appt->id), [
            'date' => $date,
            'doctor_id' => $doctor->id,
            'message' => 'Updated walk-in request note.',
        ]);

        $response->assertRedirect(route('public.booking.edit', $appt->id));
        $response->assertSessionHas('success');

        $appt->refresh();
        $this->assertNull($appt->appointment_time);
        $this->assertSame('pending', strtolower((string) $appt->status));
        $this->assertTrue((bool) ($appt->is_walk_in_request ?? false));
    }

    public function test_staff_widget_handles_walk_in_request_items(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Checkup',
            'base_price' => 1000,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $doctor = Doctor::create([
            'name' => 'Dr. Widget',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6, 7],
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $patient = $this->makePatient();

        Appointment::create([
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => null,
            'duration_minutes' => 60,
            'status' => 'pending',
            'is_walk_in_request' => true,
            'public_first_name' => 'Walk',
            'public_last_name' => 'In',
            'public_email' => 'walkin@example.com',
            'public_phone' => '09170000000',
            'public_address' => 'Sample',
        ]);

        $response = $this->actingAs($staff)->getJson(route('staff.approvals.widget', ['limit' => 8]));

        $response->assertOk();
        $response->assertJsonPath('pendingCount', 1);
        $response->assertJsonPath('items.0.time', 'Walk-in Request');
        $response->assertJsonPath('items.0.is_walk_in_request', true);
    }

    private function makePatient(): Patient
    {
        return Patient::create([
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'gender' => 'male',
            'birthdate' => '1990-01-01',
            'contact_number' => '09999999999',
            'address' => 'Test Address',
        ]);
    }

    private function makeAppointment(
        int $patientId,
        int $serviceId,
        string $date,
        string $time,
        string $status,
        ?int $doctorId = null
    ): Appointment
    {
        $appointment = Appointment::create([
            'patient_id' => $patientId,
            'service_id' => $serviceId,
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'duration_minutes' => 60,
            'status' => $status,
            'notes' => 'Test booking',
        ]);

        return $appointment;
    }
}
