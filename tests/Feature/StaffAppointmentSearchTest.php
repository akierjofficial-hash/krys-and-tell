<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAppointmentSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_linked_and_public_patients_across_all_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff', 'is_active' => true]));
        $service = Service::create(['name' => 'Dental Cleaning', 'base_price' => 1000]);
        $target = Patient::create(['first_name' => 'Jane', 'last_name' => 'Doe']);
        $this->appointment($service->id, ['patient_id' => $target->id,
            'appointment_date' => '2026-10-01', 'status' => 'approved']);
        foreach (range(1, 12) as $number) {
            $patient = Patient::create(['first_name' => "Other{$number}", 'last_name' => 'Person']);
            $this->appointment($service->id, ['patient_id' => $patient->id,
                'appointment_date' => '2026-10-02', 'status' => 'approved']);
        }
        $this->appointment($service->id, ['public_first_name' => 'Public', 'public_last_name' => 'Guest',
            'appointment_date' => '2026-10-03', 'status' => 'pending']);

        $this->get(route('staff.appointments.index'))->assertOk()
            ->assertViewHas('appointments', fn ($page) => $page->total() === 14 && $page->count() === 10)
            ->assertSee('page=2');
        $this->get(route('staff.appointments.index', ['q' => 'DOE, JANE']))->assertOk()
            ->assertViewHas('appointments', fn ($page) => $page->total() === 1)
            ->assertSeeText('Jane Doe');
        $this->get(route('staff.appointments.index', ['q' => 'GUEST PUBLIC']))->assertOk()
            ->assertViewHas('appointments', fn ($page) => $page->total() === 1)
            ->assertSeeText('Public Guest');
        $this->get(route('staff.appointments.index', ['q' => 'JANE', 'date_from' => '2026-11-01']))
            ->assertOk()->assertViewHas('appointments', fn ($page) => $page->total() === 0);
        $this->get(route('staff.appointments.index', ['q' => 'Nobody Matches']))
            ->assertOk()->assertSeeText('No appointments found.');
    }

    public function test_sort_orders_the_full_result_set_and_preserves_search_on_later_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff', 'is_active' => true]));
        $service = Service::create(['name' => 'Cleaning', 'base_price' => 1000]);
        foreach (range(1, 12) as $number) {
            $patient = Patient::create(['first_name' => 'Matching', 'last_name' => sprintf('Name%02d', $number)]);
            $this->appointment($service->id, ['patient_id' => $patient->id,
                'appointment_date' => '2026-10-01', 'status' => 'approved']);
        }

        $first = $this->get(route('staff.appointments.index', ['q' => 'matching', 'sort' => 'patient_desc']));
        $first->assertOk()->assertViewHas('appointments', fn ($page) => $page->total() === 12
            && $page->first()->patient->last_name === 'Name12')
            ->assertSee('q=matching', false)->assertSee('sort=patient_desc', false);

        $second = $this->get(route('staff.appointments.index', ['q' => 'matching', 'sort' => 'patient_desc', 'page' => 2]));
        $second->assertOk()->assertViewHas('appointments', fn ($page) => $page->count() === 2
            && $page->first()->patient->last_name === 'Name02');

        $this->get(route('staff.appointments.index', ['q' => 'not found', 'sort' => 'status_asc']))
            ->assertOk()->assertSeeText('No appointments found.');
        $this->actingAs(User::factory()->create(['role' => 'user', 'is_active' => true]))
            ->get(route('staff.appointments.index', ['q' => 'matching']))->assertForbidden();
    }

    private function appointment(int $serviceId, array $attributes): Appointment
    {
        $appointment = new Appointment();
        $appointment->forceFill(array_merge(['service_id' => $serviceId, 'appointment_time' => '10:00'], $attributes));
        $appointment->save();

        return $appointment;
    }
}
