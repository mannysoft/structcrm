<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\Budget;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

function budgetPayload(Project $project): array
{
    return [
        'project_id' => $project->id,
        'type' => BudgetType::Original->value,
        'status' => BudgetStatus::Approved->value,
        'items' => [
            ['description' => 'Materials', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 8000000],
            ['description' => 'Labor', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 3000000],
        ],
    ];
}

test('a user can create a budget', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/budgets', budgetPayload($project))->assertCreated();

    $this->assertDatabaseHas('budgets', [
        'company_id' => $company->id,
        'project_id' => $project->id,
        'is_current' => true,
    ]);

    $this->assertDatabaseHas('budget_items', ['description' => 'Materials']);
});

test('a user can list their budgets', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    foreach (range(1, 2) as $i) {
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        Budget::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);
    }

    $this->actingAs($user)
        ->getJson('/api/budgets')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can update their budget', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $budget = Budget::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

    $this->actingAs($user)
        ->putJson('/api/budgets/'.$budget->uuid, budgetPayload($project))
        ->assertOk()
        ->assertJsonPath('status', BudgetStatus::Approved->value);
});

test('a user can delete their budget', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $budget = Budget::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

    $this->actingAs($user)
        ->deleteJson('/api/budgets/'.$budget->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('budgets', ['id' => $budget->id]);
});

test('a user cannot access another tenants budget', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $budgetB = Budget::factory()->create(['company_id' => $companyB->id, 'project_id' => $projectB->id]);

    $this->actingAs($userA)
        ->getJson('/api/budgets/'.$budgetB->uuid)
        ->assertNotFound();
});
