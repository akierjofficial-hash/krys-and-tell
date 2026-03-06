<?php

namespace Tests\Feature;

use App\Models\Appointment;
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

    private function makeAppointment(int $patientId, int $serviceId, string $date, string $time, string $status): Appointment
    {
        $appointment = Appointment::create([
            'patient_id' => $patientId,
            'service_id' => $serviceId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'duration_minutes' => 60,
            'status' => $status,
            'notes' => 'Test booking',
        ]);

        return $appointment;
    }
}

