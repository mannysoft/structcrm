<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Models\User;

test('change orders page requires authentication', function () {

    $this->get('/change-orders')->assertRedirect('/login');
});

test('change orders page displays change orders', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    ChangeOrder::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'description' => 'Extra concrete work',
    ]);

    $this->actingAs($user)
        ->get('/change-orders')
        ->assertOk()
        ->assertSee('Extra concrete work');
});
