<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ActivityLog;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListSearchCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_and_account_lists_search_filter_and_paginate(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);

        foreach (range(1, 13) as $number) {
            Patient::create(['first_name' => 'Searchable', 'last_name' => sprintf('Patient%02d', $number)]);
        }
        $this->get(route('admin.patients.index', ['q' => 'Searchable', 'page' => 2]))
            ->assertOk()->assertViewHas('patients', fn ($page) => $page->total() === 13 && $page->count() === 1);
        $this->get(route('admin.patients.index', ['q' => 'NobodyMatches']))
            ->assertOk()->assertViewHas('patients', fn ($page) => $page->total() === 0);
        $this->get(route('admin.patients.index', ['q' => 'patient01, SEARCHABLE']))
            ->assertOk()->assertViewHas('patients', fn ($page) => $page->total() === 1);

        $service = Service::create(['name' => 'Dental Cleaning', 'base_price' => 1000]);
        $appointment = new Appointment();
        $appointment->forceFill(['patient_id' => Patient::firstOrFail()->id, 'service_id' => $service->id,
            'appointment_date' => '2026-10-01', 'appointment_time' => '10:00',
            'status' => 'approved', 'dentist_name' => 'Dr. Example']);
        $appointment->save();
        $this->get(route('admin.appointments.index', ['q' => 'patient01 searchable', 'status' => 'approved']))
            ->assertOk()->assertViewHas('appointments', fn ($page) => $page->total() === 1);
        $this->get(route('admin.appointments.index', ['q' => 'PATIENT01, SEARCHABLE', 'status' => 'cancelled']))
            ->assertOk()->assertViewHas('appointments', fn ($page) => $page->total() === 0);

        $public = new Appointment();
        $public->forceFill(['service_id' => $service->id, 'public_first_name' => 'Unlinked',
            'public_last_name' => 'Visitor', 'appointment_date' => '2026-10-02',
            'status' => 'pending', 'dentist_name' => 'Dr. Example']);
        $public->save();
        $this->get(route('admin.appointments.index', ['q' => 'VISITOR, UNLINKED', 'status' => 'pending']))
            ->assertOk()->assertViewHas('appointments', fn ($page) => $page->total() === 1)
            ->assertSeeText('Unlinked Visitor');

        $linkedDoctor = Doctor::create(['name' => 'Dr. Linked', 'is_active' => true]);
        $withDoctor = new Appointment();
        $withDoctor->forceFill(['service_id' => $service->id, 'patient_id' => Patient::firstOrFail()->id,
            'doctor_id' => $linkedDoctor->id, 'appointment_date' => '2026-10-03',
            'status' => 'approved']);
        $withDoctor->save();
        $this->get(route('admin.appointments.index', ['doctor' => 'Dr. Linked', 'status' => 'approved']))
            ->assertOk()->assertViewHas('appointments', fn ($page) => $page->total() === 1)
            ->assertSeeText('Dr. Linked');
        $this->get(route('admin.appointments.index', ['q' => 'DR. LINKED', 'doctor' => 'Dr. Linked']))
            ->assertOk()->assertViewHas('appointments', fn ($page) => $page->total() === 1);

        Doctor::create(['name' => 'Dr. Specialty', 'specialty' => 'Orthodontics', 'is_active' => true]);
        $this->get(route('admin.doctors.index', ['q' => 'ORTHODONTICS', 'status' => 'active']))
            ->assertOk()->assertViewHas('doctors', fn ($page) => $page->total() === 1);

        $this->actingAs(User::factory()->create(['role' => 'staff', 'is_active' => true]))
            ->get(route('admin.appointments.index', ['q' => 'Visitor']))->assertForbidden();
        $this->actingAs($admin);

        User::factory()->create(['name' => 'Searchable Staff', 'role' => 'staff', 'is_active' => true]);
        $this->get(route('admin.users.index', ['q' => 'Searchable', 'role' => 'staff', 'status' => 'active']))
            ->assertOk()->assertViewHas('users', fn ($page) => $page->total() === 1);
        $this->get(route('admin.users.index', ['q' => 'SEARCHABLE', 'role' => 'staff', 'status' => 'active']))
            ->assertOk()->assertViewHas('users', fn ($page) => $page->total() === 1);
        $this->get(route('admin.users.index', ['q' => 'Searchable', 'role' => 'staff', 'status' => 'inactive']))
            ->assertOk()->assertViewHas('users', fn ($page) => $page->total() === 0);
    }

    public function test_admin_website_deleted_and_activity_filters_keep_scope_and_empty_states(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);

        foreach (range(1, 17) as $number) {
            User::factory()->create(['role' => 'user', 'is_active' => true,
                'name' => sprintf('Website Candidate %02d', $number),
                'email' => sprintf('website%02d@example.test', $number)]);
        }
        $this->get(route('admin.user_accounts.index', ['q' => 'WEBSITE CANDIDATE', 'status' => 'active', 'page' => 2]))
            ->assertOk()->assertViewHas('users', fn ($page) => $page->total() === 17 && $page->count() === 2)
            ->assertSee('q=WEBSITE', false);
        $this->get(route('admin.user_accounts.index', ['q' => 'WEBSITE CANDIDATE', 'status' => 'inactive']))
            ->assertOk()->assertViewHas('users', fn ($page) => $page->total() === 0);

        $deleted = User::factory()->create(['role' => 'staff', 'name' => 'Deleted Example']);
        $deleted->delete();
        $this->get(route('admin.deleted_accounts.index', ['q' => 'DELETED', 'type' => 'clinic']))
            ->assertOk()->assertViewHas('accounts', fn ($page) => $page->total() === 1);
        $this->get(route('admin.deleted_accounts.index', ['q' => 'DELETED', 'type' => 'website']))
            ->assertOk()->assertViewHas('accounts', fn ($page) => $page->total() === 0);

        ActivityLog::create(['user_id' => $admin->id, 'event' => 'records_reviewed',
            'description' => 'Reviewed search audit', 'reason' => 'Test',
            'url' => '/admin/activity', 'method' => 'GET',
            'is_sensitive' => true, 'succeeded' => true, 'created_at' => '2026-09-29 09:00:00']);
        $this->get(route('admin.activity.index', ['q' => 'SEARCH AUDIT', 'result' => 'success',
            'sensitive' => 1, 'from' => '2026-09-29', 'to' => '2026-09-29']))
            ->assertOk()->assertViewHas('logs', fn ($page) => $page->total() === 1);
        $this->get(route('admin.activity.index', ['q' => 'SEARCH AUDIT', 'result' => 'failure']))
            ->assertOk()->assertViewHas('logs', fn ($page) => $page->total() === 0);
    }
}
