<?php

namespace Tests\Feature;

use App\Models\Patient;
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

        User::factory()->create(['name' => 'Searchable Staff', 'role' => 'staff', 'is_active' => true]);
        $this->get(route('admin.users.index', ['q' => 'Searchable', 'role' => 'staff', 'status' => 'active']))
            ->assertOk()->assertViewHas('users', fn ($page) => $page->total() === 1);
        $this->get(route('admin.users.index', ['q' => 'Searchable', 'role' => 'staff', 'status' => 'inactive']))
            ->assertOk()->assertViewHas('users', fn ($page) => $page->total() === 0);
    }
}
