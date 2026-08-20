<?php

declare(strict_types=1);

namespace App\Domains\Finance\Services;

use App\Domains\Finance\DTOs\ProjectProfitabilityData;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\Budget;
use App\Domains\Finance\Models\Contract;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Expense;

final class ProjectProfitabilityService
{
    public function forProject(Project $project): ProjectProfitabilityData
    {
        $currency = $project->company?->currency?->value ?? 'PHP';

        $contract = Contract::where('project_id', $project->id)->first();
        $originalContractValue = $contract?->contract_value_amount ?? 0;

        $approvedChangeOrders = (int) ChangeOrder::where('project_id', $project->id)
            ->where('status', ChangeOrderStatus::Approved->value)
            ->sum('amount');

        $revisedContractValue = $originalContractValue + $approvedChangeOrders;

        $originalBudget = $this->budgetTotal($project->id, BudgetType::Original);
        $revisedBudget = $this->budgetTotal($project->id);
        $actualCost = (int) Expense::where('project_id', $project->id)->sum('amount');

        $currentBudget = Budget::where('project_id', $project->id)
            ->where('is_current', true)
            ->first();

        $forecastToComplete = $currentBudget?->forecast_amount
            ?? max(0, $revisedBudget - $actualCost);

        $projectedFinalCost = $actualCost + $forecastToComplete;
        $projectedProfit = $revisedContractValue - $projectedFinalCost;
        $projectedMargin = $revisedContractValue > 0
            ? round(($projectedProfit / $revisedContractValue) * 100, 2)
            : 0.0;
        $costVariance = $revisedBudget - $projectedFinalCost;

        return new ProjectProfitabilityData(
            projectId: $project->id,
            originalContractValue: $originalContractValue,
            revisedContractValue: $revisedContractValue,
            originalBudget: $originalBudget,
            revisedBudget: $revisedBudget,
            actualCost: $actualCost,
            forecastToComplete: $forecastToComplete,
            projectedFinalCost: $projectedFinalCost,
            projectedProfit: $projectedProfit,
            projectedMargin: $projectedMargin,
            costVariance: $costVariance,
            currency: $currency,
        );
    }

    private function budgetTotal(int $projectId, ?BudgetType $type = null): int
    {
        $budget = Budget::query()
            ->where('project_id', $projectId)
            ->when($type !== null, fn ($query) => $query->where('type', $type))
            ->when($type === null, fn ($query) => $query->where('is_current', true))
            ->with('items')
            ->first();

        return $budget ? (int) $budget->items->sum('total') : 0;
    }
}
