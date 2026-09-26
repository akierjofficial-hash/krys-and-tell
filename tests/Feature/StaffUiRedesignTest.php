<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffUiRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_primary_staff_screens_use_the_scoped_staff_design_system(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $patient = Patient::create(['first_name' => 'UI', 'last_name' => 'Patient']);
        Doctor::create(['name' => 'Dr. Interface', 'is_active' => true]);
        Service::create(['name' => 'Cleaning', 'base_price' => 1000]);
        $this->actingAs($staff);

        $screens = [
            route('staff.dashboard'),
            route('staff.patients.index'),
            route('staff.patients.create'),
            route('staff.patients.show', $patient),
            route('staff.visits.index'),
            route('staff.payments.index'),
            route('staff.payments.choose'),
            route('staff.payments.create.cash'),
            route('staff.payments.create.installment'),
            route('staff.appointments.index'),
            route('staff.appointments.create'),
            route('staff.approvals.index'),
            route('staff.messages.index'),
            route('staff.dentist-unavailability.index'),
            route('staff.services.index'),
            route('staff.services.create'),
            route('staff.service_doctor_assignments.index'),
            route('staff.records.index'),
            route('staff.records.index', ['patient_id' => $patient->id]),
        ];

        foreach ($screens as $screen) {
            $this->get($screen)
                ->assertOk()
                ->assertSee('class="kt-staff"', false)
                ->assertSee('css/staff-app.css', false)
                ->assertSee('Booking Requests');
        }
    }

    public function test_staff_styles_do_not_leak_into_admin_or_public_pages(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('css/staff-app.css', false)
            ->assertDontSee('class="kt-staff"', false);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('css/staff-app.css', false)
            ->assertDontSee('class="kt-staff"', false);
    }
}
