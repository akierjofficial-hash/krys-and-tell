<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitProcedure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveListSearchTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff', 'is_active' => true]));
    }

    public function test_staff_lists_search_the_database_before_pagination_and_clear_normally(): void
    {
        $this->staff();
        foreach (range(1, 27) as $number) {
            Service::create(['name' => sprintf('Cleaning %02d', $number), 'base_price' => 1000]);
        }
        Service::create(['name' => 'Braces', 'base_price' => 40000]);

        $this->get(route('staff.services.index', ['q' => 'CLEANING', 'page' => 2]))
            ->assertOk()->assertViewHas('services', fn ($page) => $page->total() === 27 && $page->count() === 2)
            ->assertSee('data-live-search', false);
        $this->get(route('staff.services.index', ['q' => 'missing']))
            ->assertOk()->assertSeeText('No services found.');
        $this->get(route('staff.services.index'))
            ->assertOk()->assertViewHas('services', fn ($page) => $page->total() === 28);

        $patient = Patient::create(['first_name' => 'Fern', 'last_name' => 'Porpio']);
        Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-01', 'notes' => 'Adjustment']);
        $this->get(route('staff.visits.index', ['q' => 'PORPIO, FERN']))
            ->assertOk()->assertViewHas('patients', fn ($page) => $page->total() === 1);
        $this->get(route('staff.visits.index', ['view' => 'all', 'q' => 'adjustment']))
            ->assertOk()->assertViewHas('visits', fn ($page) => $page->total() === 1);
        $this->get(route('staff.visits.index', ['view' => 'all', 'q' => 'unknown']))
            ->assertOk()->assertSeeText('No visits found.');

        ContactMessage::create(['name' => 'Fern Porpio', 'email' => 'fern@example.test', 'message' => 'Hello']);
        $this->get(route('staff.messages.index', ['q' => 'FERN']))->assertOk()->assertSeeText('Fern Porpio');
        $this->get(route('staff.messages.index', ['q' => 'unknown']))
            ->assertOk()->assertSeeText('No messages match your search.');
    }

    public function test_live_list_requests_still_require_the_existing_roles(): void
    {
        $this->get(route('staff.services.index', ['q' => 'Braces']))->assertRedirect();
        $this->get(route('admin.doctors.index', ['q' => 'Dentist']))->assertRedirect();

        $this->staff();
        $this->get(route('admin.doctors.index', ['q' => 'Dentist']))->assertForbidden();
    }

    public function test_service_folder_and_visit_filters_search_all_pages(): void
    {
        $this->staff();
        $service = Service::create(['name' => 'Oral Prophylaxis', 'base_price' => 1000]);
        foreach (range(1, 27) as $number) {
            $patient = Patient::create(['first_name' => 'Fern', 'last_name' => sprintf('Family%02d', $number)]);
            $visit = Visit::create(['patient_id' => $patient->id, 'visit_date' => '2026-09-12']);
            VisitProcedure::create(['visit_id' => $visit->id, 'service_id' => $service->id, 'price' => 1000]);
        }

        $this->get(route('staff.services.patients', [$service, 'q' => 'FAMILY, FERN', 'page' => 2]))
            ->assertOk()->assertViewHas('patients', fn ($page) => $page->total() === 27 && $page->count() === 2);
        $this->get(route('staff.visits.index', ['view' => 'all', 'q' => 'PROPHYLAXIS',
            'date_from' => '2026-09-01', 'date_to' => '2026-09-30', 'page' => 2]))
            ->assertOk()->assertViewHas('visits', fn ($page) => $page->total() === 27 && $page->count() === 2);
        $this->get(route('staff.visits.index', ['view' => 'all', 'q' => 'PROPHYLAXIS',
            'date_from' => '2026-10-01']))
            ->assertOk()->assertViewHas('visits', fn ($page) => $page->total() === 0);
    }

    public function test_authenticated_live_requests_return_html_result_regions(): void
    {
        $this->staff();
        $headers = ['Accept' => 'text/html', 'X-Requested-With' => 'XMLHttpRequest'];
        foreach (['staff.patients.index', 'staff.visits.index', 'staff.services.index',
            'staff.appointments.index', 'staff.payments.index', 'staff.messages.index'] as $route) {
            $this->get(route($route, ['q' => 'NoMatch']), $headers)
                ->assertOk()->assertSee('data-live-results', false);
        }
        $patient = Patient::create(['first_name' => 'Fern', 'last_name' => 'Porpio']);
        $this->get(route('staff.patients.account-links.index', [$patient, 'q' => 'NoMatch']), $headers)
            ->assertOk()->assertSee('data-live-results', false);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);
        foreach (['admin.patients.index', 'admin.appointments.index', 'admin.doctors.index',
            'admin.users.index', 'admin.user_accounts.index', 'admin.deleted_accounts.index',
            'admin.activity.index'] as $route) {
            $this->get(route($route, ['q' => 'NoMatch']), $headers)
                ->assertOk()->assertSee('data-live-results', false);
        }
    }

    public function test_live_search_returns_small_authorized_fragments_with_existing_filters(): void
    {
        $headers = ['Accept' => 'text/html', 'X-Requested-With' => 'XMLHttpRequest', 'X-KT-Live-Search' => '1'];
        $this->staff();
        foreach (['staff.patients.index', 'staff.visits.index', 'staff.appointments.index',
            'staff.payments.index', 'staff.services.index', 'staff.messages.index'] as $route) {
            $response = $this->get(route($route, ['q' => 'NoMatch', 'page' => 1]), $headers)
                ->assertOk()->assertSee('data-live-results', false)->assertDontSee('<html', false);
            $this->assertLessThan(50000, strlen($response->getContent()), $route.' should omit the shared layout.');
        }
        $this->get(route('staff.payments.index', ['tab' => 'plans', 'q' => 'NoMatch']), $headers)
            ->assertOk()->assertSee('data-live-results', false)->assertSeeText('No installment plans match these filters.')
            ->assertDontSee('Received today');

        $patient = Patient::create(['first_name' => 'Sample', 'last_name' => 'Record']);
        $service = Service::create(['name' => 'Sample treatment', 'base_price' => 1000]);
        $this->get(route('staff.patients.account-links.index', [$patient, 'q' => 'NoMatch']), $headers)
            ->assertOk()->assertSee('data-live-results', false)->assertDontSee('<html', false);
        $this->get(route('staff.services.patients', [$service, 'q' => 'NoMatch']), $headers)
            ->assertOk()->assertSee('data-live-results', false)->assertDontSee('<html', false);

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        foreach (['admin.patients.index', 'admin.appointments.index', 'admin.doctors.index',
            'admin.users.index', 'admin.user_accounts.index', 'admin.deleted_accounts.index',
            'admin.activity.index'] as $route) {
            $this->get(route($route, ['q' => 'NoMatch']), $headers)
                ->assertOk()->assertSee('data-live-results', false)->assertDontSee('<html', false);
        }

        $this->get(route('staff.patients.index', ['q' => 'NoMatch']), $headers)->assertForbidden();
    }

    public function test_live_patient_search_paginates_all_matching_records_for_staff_and_admin(): void
    {
        foreach (range(1, 60) as $number) {
            Patient::create(['first_name' => 'Batch', 'last_name' => sprintf('Search%02d', $number)]);
        }
        $headers = ['X-KT-Live-Search' => '1', 'X-Requested-With' => 'XMLHttpRequest'];
        $this->staff();
        $this->get(route('staff.patients.index', ['q' => 'batch', 'page' => 2, 'sort' => 'newest']), $headers)
            ->assertOk()->assertViewHas('patients', fn ($page) => $page->total() === 60 && $page->count() === 25)
            ->assertSee('data-live-results', false);

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $this->get(route('admin.patients.index', ['q' => 'BATCH', 'page' => 2]), $headers)
            ->assertOk()->assertViewHas('patients', fn ($page) => $page->total() === 60 && $page->count() === 12)
            ->assertSee('data-live-results', false);
    }
}
