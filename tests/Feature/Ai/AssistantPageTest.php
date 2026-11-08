<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;

test('the assistant page requires authentication', function () {
    $this->get('/assistant')->assertRedirect('/login');
});

test('staff users can open the assistant page', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->get('/assistant')
        ->assertOk()
        ->assertSee('AI Assistant');
});

test('client portal users cannot open the assistant', function () {
    $company = Company::factory()->create();
    $clientUser = User::factory()->client()->create(['company_id' => $company->id]);

    $this->actingAs($clientUser)
        ->get('/assistant')
        ->assertForbidden();
});

test('the dashboard renders the ask ai panel', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Ask AI');
});
