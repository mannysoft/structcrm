<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\Sales\Models\Client;
use App\Models\User;

test('tasks page requires authentication', function () {

    $this->get('/tasks')->assertRedirect('/login');
});

test('tasks page displays tasks', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    Task::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'title' => 'Pour foundation',
    ]);

    $this->actingAs($user)
        ->get('/tasks')
        ->assertOk()
        ->assertSee('Pour foundation');
});
