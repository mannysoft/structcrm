<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Models\User;

function createChangeOrder(Company $company, Project $project): ChangeOrder
{

    return ChangeOrder::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);
}

test('a user can create a change order', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/change-orders', [
        'project_id' => $project->id,
        'description' => 'Extra work',
        'amount' => 5000.00,
        'status' => ChangeOrderStatus::Pending->value,
    ])->assertCreated();

    $this->assertDatabaseHas('change_orders', [
        'company_id' => $company->id,
        'description' => 'Extra work',
    ]);
});

test('a user can list their change orders', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    ChangeOrder::factory()->count(2)->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/change-orders')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can delete their change order', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $changeOrder = createChangeOrder($company, $project);

    $this->actingAs($user)
        ->deleteJson('/api/change-orders/'.$changeOrder->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('change_orders', ['id' => $changeOrder->id]);
});

test('a user cannot view another tenants change order', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $changeOrderB = createChangeOrder($companyB, $projectB);

    $this->actingAs($user)
        ->getJson('/api/change-orders/'.$changeOrderB->uuid)
        ->assertNotFound();
});
