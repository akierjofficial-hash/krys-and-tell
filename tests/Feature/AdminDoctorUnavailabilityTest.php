<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDoctorUnavailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_dentist_day_off_record(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'name' => 'Dr. Admin Edit',
            'is_active' => true,
            'working_days' => [1, 2, 3, 4, 5, 6, 7],
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $dateA = now()->addDays(2)->toDateString();
        $dateB = now()->addDays(3)->toDateString();

        $create = $this->actingAs($admin)->post(route('admin.dentist-unavailability.store'), [
            'doctor_id' => $doctor->id,
            'unavailable_date' => $dateA,
            'reason' => 'Board meeting',
        ]);

        $create->assertRedirect(route('admin.dentist-unavailability.index'));

        $id = (int) DB::table('doctor_unavailabilities')
            ->where('doctor_id', $doctor->id)
            ->whereDate('unavailable_date', $dateA)
            ->value('id');

        $this->assertGreaterThan(0, $id);

        $update = $this->actingAs($admin)->put(route('admin.dentist-unavailability.update', $id), [
            'doctor_id' => $doctor->id,
            'unavailable_date' => $dateB,
            'reason' => 'Regional seminar',
        ]);

        $update->assertRedirect(route('admin.dentist-unavailability.index'));

        $this->assertTrue(
            DB::table('doctor_unavailabilities')
                ->where('id', $id)
                ->where('doctor_id', $doctor->id)
                ->whereDate('unavailable_date', $dateB)
                ->where('reason', 'Regional seminar')
                ->exists()
        );

        $delete = $this->actingAs($admin)->delete(route('admin.dentist-unavailability.destroy', $id));
        $delete->assertRedirect(route('admin.dentist-unavailability.index'));

        $this->assertDatabaseMissing('doctor_unavailabilities', ['id' => $id]);
    }
}
