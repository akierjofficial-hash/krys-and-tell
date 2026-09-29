<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaffPatientsListTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->actingAs($this->staff);
    }

    private function patient(string $first, string $last, array $extra = []): Patient
    {
        return Patient::create(array_merge(['first_name' => $first, 'last_name' => $last], $extra));
    }

    public function test_patients_list_is_server_paginated_with_25_rows_by_default(): void
    {
        foreach (range(1, 30) as $i) $this->patient("First{$i}", sprintf('Patient%02d', $i));
        $response = $this->get(route('staff.patients.index'))->assertOk();
        $response->assertViewHas('patients', fn ($patients) => $patients->count() === 25 && $patients->total() === 30);
        $response->assertSeeText('Showing 1–25 of 30 patients');
    }

    public function test_search_matches_names_full_name_contact_email_and_birthdate(): void
    {
        $this->patient('Fern', 'Porpio', ['middle_name' => 'Concepcion', 'contact_number' => '09171234567',
            'email' => 'fern@example.test', 'birthdate' => '1995-06-15']);
        $this->patient('Different', 'Person');
        foreach (['Fern', 'FERN', 'porpio', 'Fern Porpio', 'Porpio, Fern', 'Concepcion Porpio', '09171234567', 'FERN@EXAMPLE.TEST', '1995-06-15'] as $term) {
            $this->get(route('staff.patients.index', ['q' => $term]))->assertOk()->assertSeeText('Porpio, Fern')->assertDontSeeText('Person, Different');
        }
        $this->get(route('staff.patients.index', ['initial' => 'D']))
            ->assertSee("this.form.elements.initial.value=''", false);
        $response = $this->get(route('staff.patients.index', ['q' => 'Fern', 'initial' => 'D']));
        $response->assertSee('href="' . route('staff.patients.index') . '" aria-label="Clear search"', false);
    }

    public function test_search_operator_compiles_case_insensitively_for_postgresql(): void
    {
        $sql = DB::connection('pgsql')->table('patients')->whereLike('first_name', '%fern%')->toSql();
        $this->assertStringContainsString('ilike', strtolower($sql));
    }

    public function test_search_and_pagination_work_together(): void
    {
        foreach (range(1, 28) as $i) $this->patient("Match{$i}", sprintf('Search%02d', $i));
        $this->patient('Excluded', 'Record');
        $response = $this->get(route('staff.patients.index', ['q' => 'Match', 'page' => 2]))->assertOk();
        $response->assertViewHas('patients', fn ($patients) => $patients->count() === 3 && $patients->total() === 28 && $patients->currentPage() === 2);
    }

    public function test_last_name_initial_filters_across_the_database(): void
    {
        foreach (range(1, 30) as $i) $this->patient("Other{$i}", "Adams{$i}");
        $this->patient('Target', 'Diones');
        $response = $this->get(route('staff.patients.index', ['initial' => 'D']))->assertOk();
        $response->assertSeeText('Diones, Target')->assertDontSeeText('Adams1, Other1');
        $response->assertViewHas('availableInitials', fn ($letters) => in_array('A', $letters, true) && in_array('D', $letters, true));
    }

    public function test_sort_is_allowlisted_and_stable(): void
    {
        $this->patient('Zed', 'Zulu');
        $this->patient('Amy', 'Alpha');
        $this->get(route('staff.patients.index', ['sort' => 'last_desc']))->assertSeeInOrder(['Zulu, Zed', 'Alpha, Amy']);
        $this->get(route('staff.patients.index', ['sort' => 'patients; DROP TABLE patients']))
            ->assertOk()->assertViewHas('sort', 'last_asc')->assertSeeInOrder(['Alpha, Amy', 'Zulu, Zed']);
        $this->assertDatabaseCount('patients', 2);
    }

    public function test_pagination_links_preserve_all_list_parameters(): void
    {
        foreach (range(1, 30) as $i) $this->patient("Alpha{$i}", "Able{$i}");
        $response = $this->get(route('staff.patients.index', ['q' => 'Alpha', 'sort' => 'last_desc', 'initial' => 'A', 'per_page' => 25]));
        $response->assertViewHas('patients', function ($patients) {
            $url = html_entity_decode($patients->url(2));
            return str_contains($url, 'q=Alpha') && str_contains($url, 'sort=last_desc')
                && str_contains($url, 'initial=A') && str_contains($url, 'per_page=25');
        });
    }

    public function test_rows_per_page_accepts_only_25_50_or_100(): void
    {
        foreach (range(1, 60) as $i) $this->patient("First{$i}", "Rows{$i}");
        $this->get(route('staff.patients.index', ['per_page' => 50]))
            ->assertViewHas('patients', fn ($patients) => $patients->count() === 50)->assertViewHas('perPage', 50);
        $this->get(route('staff.patients.index', ['per_page' => 5000]))
            ->assertViewHas('patients', fn ($patients) => $patients->count() === 25)->assertViewHas('perPage', 25);
    }

    public function test_last_visit_uses_one_aggregate_query_and_displays_latest_date(): void
    {
        $patient = $this->patient('Visited', 'Patient');
        Visit::create(['patient_id' => $patient->id, 'visit_date' => '2025-01-01']);
        Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-08-20']);
        $this->patient('Never', 'Visited');
        DB::flushQueryLog(); DB::enableQueryLog();
        $response = $this->get(route('staff.patients.index'))->assertOk()->assertSeeText('Aug 20, 2026')->assertSeeText('No visits yet');
        $visitQueries = collect(DB::getQueryLog())->filter(fn ($row) => str_contains(strtolower($row['query']), 'visits'));
        $this->assertLessThanOrEqual(1, $visitQueries->count());
    }

    public function test_missing_birthdate_contact_gender_and_visits_render_safely(): void
    {
        $this->patient('Sparse', 'Patient');
        $this->get(route('staff.patients.index'))->assertOk()->assertSeeText('Patient, Sparse')
            ->assertSeeText('Not specified')->assertSeeText('No contact number')->assertSeeText('No visits yet');
    }

    public function test_deleting_patient_remains_a_soft_delete_with_restore_information(): void
    {
        $patient = $this->patient('Soft', 'Delete');
        $this->delete(route('staff.patients.destroy', $patient), ['return' => route('staff.patients.index', ['q' => 'Soft'])])
            ->assertRedirect(route('staff.patients.index', ['q' => 'Soft']))->assertSessionHas('undo');
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    }

    public function test_nonstaff_users_cannot_access_staff_patient_routes(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->actingAs($user)->get(route('staff.patients.index'))->assertForbidden();
    }

    public function test_existing_create_show_and_edit_routes_still_render_with_return_state(): void
    {
        $patient = $this->patient('Route', 'Patient');
        $return = route('staff.patients.index', ['q' => 'Route', 'sort' => 'newest', 'page' => 2]);
        $this->get(route('staff.patients.create', ['return' => $return]))->assertOk();
        $this->get(route('staff.patients.show', ['patient' => $patient, 'return' => $return]))->assertOk();
        $this->get(route('staff.patients.edit', ['patient' => $patient, 'return' => $return]))->assertOk();
    }

    public function test_invalid_page_recovers_to_last_available_page(): void
    {
        foreach (range(1, 30) as $i) $this->patient("Page{$i}", "Patient{$i}");
        $this->get(route('staff.patients.index', ['page' => 99, 'q' => 'Page']))
            ->assertRedirect(route('staff.patients.index', ['q' => 'Page', 'page' => 2]));
    }
}
