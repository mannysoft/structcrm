<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\ContractStatus;
use App\Domains\Finance\Models\Contract;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

function createContract(Company $company, Project $project): Contract
{
    return Contract::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);
}

test('a user can create a contract', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/contracts', [
        'project_id' => $project->id,
        'title' => 'Main Construction Contract',
        'contract_value' => 20000000,
        'status' => ContractStatus::Signed->value,
    ])->assertCreated();

    $this->assertDatabaseHas('contracts', [
        'company_id' => $company->id,
        'title' => 'Main Construction Contract',
    ]);
});

test('a user can list their contracts', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    foreach (range(1, 2) as $i) {
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        createContract($company, $project);
    }

    $this->actingAs($user)
        ->getJson('/api/contracts')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can update their contract', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $contract = createContract($company, $project);

    $this->actingAs($user)
        ->putJson('/api/contracts/'.$contract->uuid, [
            'project_id' => $project->id,
            'title' => 'Updated Contract',
            'contract_value' => 25000000,
            'status' => ContractStatus::Signed->value,
        ])
        ->assertOk()
        ->assertJsonPath('title', 'Updated Contract');
});

test('a user can delete their contract', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $contract = createContract($company, $project);

    $this->actingAs($user)
        ->deleteJson('/api/contracts/'.$contract->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('contracts', ['id' => $contract->id]);
});

test('a user cannot access another tenants contract', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $contractB = createContract($companyB, $projectB);

    $this->actingAs($userA)
        ->getJson('/api/contracts/'.$contractB->uuid)
        ->assertNotFound();
});
