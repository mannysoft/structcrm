<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\CreateBudgetAction;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;

test('it creates a current budget with line items', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $budget = app(CreateBudgetAction::class)->execute(new BudgetData(
        projectId: $project->id,
        type: BudgetType::Original,
        status: BudgetStatus::Approved,
        items: [
            ['description' => 'Materials', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 8_000_000],
            ['description' => 'Labor', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 3_000_000],
        ],
    ), $company->id);

    $this->assertSame(1, $budget->version);
    $this->assertTrue($budget->is_current);
    $this->assertSame(11_000_000, $budget->totalAmount());
    $this->assertCount(2, $budget->items);

    $this->assertDatabaseHas('budget_items', [
        'budget_id' => $budget->id,
        'description' => 'Materials',
        'total' => 8_000_000,
    ]);
});

test('creating a revised budget supersedes the current one', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $original = app(CreateBudgetAction::class)->execute(new BudgetData(
        projectId: $project->id,
        type: BudgetType::Original,
        items: [['description' => 'Materials', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 5_000_000]],
    ), $company->id);

    $revised = app(CreateBudgetAction::class)->execute(new BudgetData(
        projectId: $project->id,
        type: BudgetType::Revised,
        items: [['description' => 'Materials', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 6_000_000]],
    ), $company->id);

    $this->assertSame(2, $revised->version);
    $this->assertTrue($revised->fresh()->is_current);
    $this->assertFalse($original->fresh()->is_current);
});
