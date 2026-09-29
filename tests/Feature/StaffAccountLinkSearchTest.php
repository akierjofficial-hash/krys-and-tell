<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAccountLinkSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_search_can_reach_matches_beyond_the_first_25_without_linking_anyone(): void
    {
        $patient = Patient::create(['first_name' => 'Link', 'last_name' => 'Candidate']);
        foreach (range(1, 27) as $number) {
            User::factory()->create(['role' => 'user', 'is_active' => true,
                'name' => sprintf('Candidate %02d', $number),
                'email' => sprintf('candidate%02d@example.test', $number)]);
        }

        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->actingAs($staff)->get(route('staff.patients.account-links.index', [$patient, 'q' => 'CANDIDATE']))
            ->assertOk()->assertViewHas('accounts', fn ($page) => $page->total() === 27
                && $page->count() === 25)
            ->assertSee('q=CANDIDATE', false);
        $this->get(route('staff.patients.account-links.index', [$patient, 'q' => 'candidate', 'page' => 2]))
            ->assertOk()->assertViewHas('accounts', fn ($page) => $page->total() === 27
                && $page->count() === 2)
            ->assertSeeText('Candidate 27');
        $this->assertDatabaseCount('patient_user_links', 0);

        $this->actingAs(User::factory()->create(['role' => 'user', 'is_active' => true]))
            ->get(route('staff.patients.account-links.index', [$patient, 'q' => 'candidate']))->assertForbidden();
    }
}
