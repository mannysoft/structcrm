<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

test('projects page requires authentication', function () {

    $this->get('/projects')->assertRedirect('/login');
});

test('projects page displays projects', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'title' => 'Skyline Tower',
    ]);

    $this->actingAs($user)
        ->get('/projects')
        ->assertOk()
        ->assertSee('Skyline Tower');
});
