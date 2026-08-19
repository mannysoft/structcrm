<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

function createProject(Company $company, Client $client): Project
{

    return Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
}

test('a user can create a project and it is scoped to their company', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/projects', [
        'title' => 'Highrise Construction',
        'client_id' => $client->id,
        'status' => ProjectStatus::Active->value,
        'budget' => 50000.50,
    ])->assertCreated();

    $this->assertDatabaseHas('projects', [
        'company_id' => $company->id,
        'title' => 'Highrise Construction',
        'budget_amount' => 5000050,
    ]);
});

test('a user can list their projects', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    Project::factory()->count(2)->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/projects')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can view their project', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $project = createProject($company, $client);

    $this->actingAs($user)
        ->getJson('/api/projects/'.$project->uuid)
        ->assertOk()
        ->assertJsonPath('title', $project->title);
});

test('a user can update their project', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $project = createProject($company, $client);

    $this->actingAs($user)
        ->putJson('/api/projects/'.$project->uuid, [
            'title' => 'Updated Title',
            'client_id' => $client->id,
            'status' => ProjectStatus::Active->value,
            'budget' => 10000,
        ])
        ->assertOk()
        ->assertJsonPath('title', 'Updated Title');
});

test('a user can delete their project', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $project = createProject($company, $client);

    $this->actingAs($user)
        ->deleteJson('/api/projects/'.$project->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('projects', ['id' => $project->id]);
});

test('a user cannot view another tenants project', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $projectB = createProject($companyB, $clientB);

    $this->actingAs($userA)
        ->getJson('/api/projects/'.$projectB->uuid)
        ->assertNotFound();
});

test('a user cannot access projects belonging to another tenant', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $projectB = createProject($companyB, $clientB);

    $this->actingAs($userA);

    $this->assertNull(Project::find($projectB->id));
});
