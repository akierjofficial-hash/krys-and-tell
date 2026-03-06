<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WalkInRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_user_can_request_walk_in_for_today_when_no_slots_are_available(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Oral Prophylaxis',
            'base_price' => 900,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $doctor = Doctor::create([
            'name' => 'Dr. Fullbooked',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6, 7],
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $date = now()->toDateString();
        $patient = $this->makePatient();
        $this->blockAllDoctorSlots($patient->id, $service->id, $doctor->id, $date);

        $response = $this->actingAs($user)->post(route('public.booking.store', $service->id), [
            'date' => $date,
            'doctor_id' => $doctor->id,
            'request_walkin' => 1,
            'full_name' => 'Walk In Requester',
            'contact' => '09171234567',
            'address' => 'Sample Address',
            'birthdate' => '1997-03-04',
            'message' => 'No slots left, requesting walk-in.',
        ]);

        $response->assertRedirect(route('public.booking.create', $service->id));

        $pending = Appointment::query()
            ->where('service_id', $service->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        $this->assertNotNull($pending);
        $this->assertNull($pending->appointment_time);
        $this->assertTrue((bool) ($pending->is_walk_in_request ?? false));
    }

    public function test_walk_in_request_is_rejected_when_slots_are_still_available(): void
    {
        Carbon::setTestNow(now()->startOfDay()->addHours(8));

        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Tooth Extraction',
            'base_price' => 1500,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $doctor = Doctor::create([
            'name' => 'Dr. Available',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6, 7],
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $date = now()->toDateString();

        $response = $this->from(route('public.booking.create', $service->id))
            ->actingAs($user)
            ->post(route('public.booking.store', $service->id), [
                'date' => $date,
                'doctor_id' => $doctor->id,
                'request_walkin' => 1,
                'full_name' => 'Walk In Requester',
                'contact' => '09171234567',
                'address' => 'Sample Address',
                'birthdate' => '1997-03-04',
                'message' => 'Trying walk-in even with available slots.',
            ]);

        $response->assertRedirect(route('public.booking.create', $service->id));
        $response->assertSessionHasErrors('request_walkin');
    }

    public function test_staff_approval_marks_walk_in_request_as_walked_in(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $patient = $this->makePatient();

        $service = Service::create([
            'name' => 'Consultation',
            'base_price' => 700,
            'allow_custom_price' => false,
            'duration_minutes' => 60,
        ]);

        $doctor = Doctor::create([
            'name' => 'Dr. Approver',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6, 7],
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => null,
            'duration_minutes' => 60,
            'status' => 'pending',
            'is_walk_in_request' => true,
            'notes' => 'Walk-in request test',
        ]);

        $response = $this->actingAs($staff)->post(route('staff.approvals.approve', $appointment), [
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '',
        ]);

        $response->assertRedirect(route('staff.approvals.index'));

        $appointment->refresh();
        $this->assertSame('walked_in', strtolower((string) $appointment->status));
        $this->assertNull($appointment->appointment_time);
    }

    private function makePatient(): Patient
    {
        return Patient::create([
            'first_name' => 'Load',
            'last_name' => 'Tester',
            'gender' => 'male',
            'birthdate' => '1991-01-01',
            'contact_number' => '09999999999',
            'address' => 'Test Address',
        ]);
    }

    private function blockAllDoctorSlots(int $patientId, int $serviceId, int $doctorId, string $date): void
    {
        foreach (range(9, 16) as $hour) {
            $time = str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00:00';

            $payload = [
                'patient_id' => $patientId,
                'service_id' => $serviceId,
                'doctor_id' => $doctorId,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'duration_minutes' => 60,
                'status' => 'upcoming',
                'notes' => 'Blocking slot',
            ];

            if (Schema::hasColumn('appointments', 'dentist_name')) {
                $payload['dentist_name'] = 'Dr. Fullbooked';
            }

            Appointment::create($payload);
        }
    }
}
