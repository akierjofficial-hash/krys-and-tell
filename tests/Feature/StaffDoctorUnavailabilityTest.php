<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaffDoctorUnavailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_save_and_remove_dentist_day_off_date(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'name' => 'Dr. Leave',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6, 7],
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $date = now()->addDays(3)->toDateString();

        $storeResponse = $this->actingAs($staff)->post(route('staff.dentist-unavailability.store'), [
            'doctor_id' => $doctor->id,
            'unavailable_date' => $date,
            'reason' => 'Team meeting',
        ]);

        $storeResponse->assertRedirect(route('staff.dentist-unavailability.index'));
        $this->assertTrue(
            DB::table('doctor_unavailabilities')
                ->where('doctor_id', $doctor->id)
                ->whereDate('unavailable_date', $date)
                ->where('reason', 'Team meeting')
                ->exists()
        );

        $rowId = (int) DB::table('doctor_unavailabilities')
            ->where('doctor_id', $doctor->id)
            ->whereDate('unavailable_date', $date)
            ->value('id');

        $deleteResponse = $this->actingAs($staff)->delete(route('staff.dentist-unavailability.destroy', $rowId));
        $deleteResponse->assertRedirect(route('staff.dentist-unavailability.index'));

        $this->assertDatabaseMissing('doctor_unavailabilities', [
            'id' => $rowId,
        ]);
    }
}
