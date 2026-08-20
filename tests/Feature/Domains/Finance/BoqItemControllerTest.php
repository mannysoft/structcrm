<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

function createBoqItem(Company $company, Project $project): BoqItem
{
    return BoqItem::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);
}

test('a user can create a boq item', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/boq-items', [
        'project_id' => $project->id,
        'description' => 'Concrete slab',
        'quantity' => 12.5,
        'unit' => 'cu.m.',
        'unit_price' => 5000,
    ])->assertCreated();

    $this->assertDatabaseHas('boq_items', [
        'company_id' => $company->id,
        'description' => 'Concrete slab',
    ]);
});

test('a user can list their boq items', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    BoqItem::factory()->count(2)->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/boq-items')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can update their boq item', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $boqItem = createBoqItem($company, $project);

    $this->actingAs($user)
        ->putJson('/api/boq-items/'.$boqItem->uuid, [
            'project_id' => $project->id,
            'description' => 'Updated item',
            'quantity' => 10,
            'unit' => 'cu.m.',
            'unit_price' => 6000,
        ])
        ->assertOk()
        ->assertJsonPath('description', 'Updated item');
});

test('a user can delete their boq item', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $boqItem = createBoqItem($company, $project);

    $this->actingAs($user)
        ->deleteJson('/api/boq-items/'.$boqItem->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('boq_items', ['id' => $boqItem->id]);
});

test('a user cannot access another tenants boq item', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $boqItemB = createBoqItem($companyB, $projectB);

    $this->actingAs($userA)
        ->getJson('/api/boq-items/'.$boqItemB->uuid)
        ->assertNotFound();
});
