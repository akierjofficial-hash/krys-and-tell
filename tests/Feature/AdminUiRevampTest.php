<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUiRevampTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_screen_renders_inside_the_shared_workspace(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $websiteUser = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $doctor = Doctor::create(['name' => 'Dr. Krys Tell', 'is_active' => true]);
        $patient = Patient::create(['first_name' => 'Sample', 'last_name' => 'Patient']);
        Service::create(['name' => 'Dental Cleaning', 'base_price' => 1000, 'duration_minutes' => 30]);
        $request = new Appointment();
        $request->forceFill([
            'doctor_id' => $doctor->id,
            'service_id' => Service::first()->id,
            'appointment_date' => today()->addDay(),
            'appointment_time' => '10:00',
            'status' => 'pending',
            'public_first_name' => 'Pending',
            'public_last_name' => 'Patient',
            'public_email' => 'pending@example.test',
        ]);
        $request->save();

        $routes = [
            route('admin.dashboard'),
            route('admin.analytics.index'),
            route('admin.approvals.index'),
            route('admin.schedule.index'),
            route('admin.appointments.index'),
            route('admin.patients.index'),
            route('admin.patients.show', $patient),
            route('admin.doctors.index'),
            route('admin.doctors.create'),
            route('admin.doctors.edit', $doctor),
            route('admin.service_doctor_assignments.index'),
            route('admin.dentist-unavailability.index'),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.edit', $staff),
            route('admin.users.activity', $staff),
            route('admin.user_accounts.index'),
            route('admin.user_accounts.edit', $websiteUser),
            route('admin.activity.index'),
            route('admin.deleted_accounts.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)
                ->assertOk()
                ->assertSee('class="admin-app"', false)
                ->assertSee('css/admin-app.css', false)
                ->assertSee('Admin Workspace');
        }
    }

    public function test_admin_shell_exposes_accessible_navigation_and_theme_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('href="#adminMain"', false)
            ->assertSee('aria-controls="adminSidebar"', false)
            ->assertSee('Toggle dark mode')
            ->assertSee('Clinic overview')
            ->assertSee('Manage my account');
    }

    public function test_admin_views_do_not_use_native_confirmation_dialogs_or_fixed_calendar_height(): void
    {
        $views = collect([
            ...glob(resource_path('views/admin/**/*.blade.php')),
            ...glob(resource_path('views/admin/*.blade.php')),
            resource_path('views/shared/dentist_unavailability/index.blade.php'),
        ]);

        foreach ($views as $view) {
            $contents = file_get_contents($view);
            $this->assertStringNotContainsString('confirm(', $contents, $view);
            $this->assertStringNotContainsString('height: 760', $contents, $view);
        }
    }
}
