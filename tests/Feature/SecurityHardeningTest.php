<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_login_via_user_portal(): void
    {
        $password = 'StrongPass123!';

        User::factory()->create([
            'email' => 'inactive-user@example.com',
            'password' => Hash::make($password),
            'role' => 'user',
            'is_active' => false,
        ]);

        $response = $this->post(route('userlogin.submit'), [
            'email' => 'inactive-user@example.com',
            'password' => $password,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_login_rejects_protocol_relative_redirects(): void
    {
        $password = 'StrongPass123!';

        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make($password),
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->post(route('userlogin.submit'), [
            'email' => 'user@example.com',
            'password' => $password,
            'redirect' => '//evil.example',
        ]);

        $response->assertRedirect(route('public.services.index'));
    }

    public function test_user_login_allows_safe_internal_redirect(): void
    {
        $password = 'StrongPass123!';

        User::factory()->create([
            'email' => 'safe-redirect-user@example.com',
            'password' => Hash::make($password),
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->post(route('userlogin.submit'), [
            'email' => 'safe-redirect-user@example.com',
            'password' => $password,
            'redirect' => '/services',
        ]);

        $response->assertRedirect('/services');
    }

    public function test_push_unsubscribe_cannot_delete_another_users_subscription(): void
    {
        $actor = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $owner = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $endpoint = 'https://push.example/subscription/abc123';

        PushSubscription::create([
            'user_id' => $owner->id,
            'role' => 'staff',
            'endpoint' => $endpoint,
            'public_key' => 'public-key',
            'auth_token' => 'auth-token',
            'content_encoding' => 'aesgcm',
        ]);

        $this->actingAs($actor)
            ->post(route('push.unsubscribe'), [
                'endpoint' => $endpoint,
            ])
            ->assertOk();

        $this->assertDatabaseHas('push_subscriptions', [
            'endpoint' => $endpoint,
            'user_id' => $owner->id,
        ]);
    }
}
