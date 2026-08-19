<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Expense;
use App\Models\User;

function createExpense(Company $company, Project $project): Expense
{

    return Expense::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);
}

test('a user can create an expense', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/expenses', [
        'project_id' => $project->id,
        'category' => ExpenseCategory::Labor->value,
        'amount' => 250.75,
        'date' => '2026-08-18',
    ])->assertCreated();

    $this->assertDatabaseHas('expenses', [
        'company_id' => $company->id,
        'amount' => 25075,
    ]);
});

test('a user can list their expenses', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    Expense::factory()->count(2)->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/expenses')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can delete their expense', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $expense = createExpense($company, $project);

    $this->actingAs($user)
        ->deleteJson('/api/expenses/'.$expense->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
});

test('a user cannot view another tenants expense', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $expenseB = createExpense($companyB, $projectB);

    $this->actingAs($user)
        ->getJson('/api/expenses/'.$expenseB->uuid)
        ->assertNotFound();
});
