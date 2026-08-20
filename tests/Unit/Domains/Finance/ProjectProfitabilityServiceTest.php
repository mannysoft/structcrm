<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\CreateBudgetAction;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Services\ProjectProfitabilityService;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Expense;

test('it computes project profitability', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    Contract::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'contract_value_amount' => 1_500_000_000,
        'contract_value_currency' => 'PHP',
    ]);

    app(CreateBudgetAction::class)->execute(new BudgetData(
        projectId: $project->id,
        items: [['description' => 'Total works', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 1_200_000_000]],
    ), $company->id);

    Expense::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'amount' => 720_000_000,
        'currency' => 'PHP',
    ]);

    $profitability = app(ProjectProfitabilityService::class)->forProject($project);

    $this->assertSame(1_500_000_000, $profitability->originalContractValue);
    $this->assertSame(1_500_000_000, $profitability->revisedContractValue);
    $this->assertSame(1_200_000_000, $profitability->revisedBudget);
    $this->assertSame(720_000_000, $profitability->actualCost);
    $this->assertSame(480_000_000, $profitability->forecastToComplete);
    $this->assertSame(1_200_000_000, $profitability->projectedFinalCost);
    $this->assertSame(300_000_000, $profitability->projectedProfit);
    $this->assertSame(20.0, $profitability->projectedMargin);
    $this->assertSame(0, $profitability->costVariance);
    $this->assertSame('PHP', $profitability->currency);
});
