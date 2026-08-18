<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createExpense(Company $company, Project $project): Expense
    {
        return Expense::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);
    }

    public function test_a_user_can_create_an_expense(): void
    {
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
    }

    public function test_a_user_can_list_their_expenses(): void
    {
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
    }

    public function test_a_user_can_delete_their_expense(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $expense = $this->createExpense($company, $project);

        $this->actingAs($user)
            ->deleteJson('/api/expenses/'.$expense->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_a_user_cannot_view_another_tenants_expense(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $expenseB = $this->createExpense($companyB, $projectB);

        $this->actingAs($user)
            ->getJson('/api/expenses/'.$expenseB->uuid)
            ->assertNotFound();
    }
}
