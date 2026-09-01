<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\CreateBudgetAction;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Models\Contract;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Expense;
use App\Models\User;

test('dashboard shows cost variance', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $user = User::factory()->create(['company_id' => $company->id]);

    Contract::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'contract_value_amount' => 1_500_000_000,
        'contract_value_currency' => 'PHP',
    ]);

    app(CreateBudgetAction::class)->execute(new BudgetData(
        projectId: $project->id,
        items: [['description' => 'Total works', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 1_000_000_000]],
    ), $company->id);

    Expense::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'amount' => 1_300_000_000,
        'currency' => 'PHP',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Cost Variance')
        ->assertSee('3,000,000.00');
});
