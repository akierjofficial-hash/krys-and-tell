<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SecurityHeadersAndUploadLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_includes_security_headers(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_patients_import_rejects_file_over_10mb(): void
    {
        $staff = $this->makeStaff();

        $response = $this->actingAs($staff)
            ->post(route('staff.patients.import'), [
                'file' => UploadedFile::fake()->create('too-big.csv', 11000, 'text/csv'),
            ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_visits_import_rejects_file_over_10mb(): void
    {
        $staff = $this->makeStaff();

        $response = $this->actingAs($staff)
            ->post(route('staff.visits.import'), [
                'file' => UploadedFile::fake()->create('too-big.csv', 11000, 'text/csv'),
            ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_installment_plan_import_rejects_file_over_10mb(): void
    {
        $staff = $this->makeStaff();

        $response = $this->actingAs($staff)
            ->post(route('staff.installments.import'), [
                'file' => UploadedFile::fake()->create('too-big.csv', 11000, 'text/csv'),
            ]);

        $response->assertSessionHasErrors('file');
    }

    private function makeStaff(): User
    {
        return User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);
    }
}
