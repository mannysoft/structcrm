<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;

test('companies page requires authentication', function () {

    $this->get('/companies')->assertRedirect('/login');
});

test('companies page displays the current company', function () {

    $company = Company::factory()->create(['name' => 'Acme Builders']);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->get('/companies')
        ->assertOk()
        ->assertSee('Acme Builders');
});
