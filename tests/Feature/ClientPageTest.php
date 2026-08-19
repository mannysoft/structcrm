<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;

test('clients page requires authentication', function () {

    $this->get('/clients')->assertRedirect('/login');
});

test('clients page displays clients', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    Client::factory()->create(['company_id' => $company->id, 'name' => 'Jane Doe']);

    $this->actingAs($user)
        ->get('/clients')
        ->assertOk()
        ->assertSee('Jane Doe');
});
