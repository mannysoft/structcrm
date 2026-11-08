<?php

declare(strict_types=1);

namespace App\Ai\Tools\Analytics;

use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Services\ProjectProfitabilityService;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetProjectProfitability implements Tool
{
    use InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Calculate profitability for one project: original and revised contract value, budget, actual cost, forecast to complete, projected profit, margin percentage and cost variance. Accepts the project UUID or project number.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $request->validate([
            'project' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($request): array {
            $project = $this->findRecord(Project::class, $request['project'], 'project', ['project_number']);
            $this->authorize('view', $project);

            $data = app(ProjectProfitabilityService::class)->forProject($project);

            return [
                'project' => $project->title,
                'project_uuid' => $project->uuid,
                'project_number' => $project->project_number,
                'currency' => $data->currency,
                'original_contract_value' => $this->toDecimal($data->originalContractValue),
                'revised_contract_value' => $this->toDecimal($data->revisedContractValue),
                'original_budget' => $this->toDecimal($data->originalBudget),
                'revised_budget' => $this->toDecimal($data->revisedBudget),
                'actual_cost' => $this->toDecimal($data->actualCost),
                'forecast_to_complete' => $this->toDecimal($data->forecastToComplete),
                'projected_final_cost' => $this->toDecimal($data->projectedFinalCost),
                'projected_profit' => $this->toDecimal($data->projectedProfit),
                'projected_margin_percent' => $data->projectedMargin,
                'cost_variance' => $this->toDecimal($data->costVariance),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('Project UUID or project number.')->required(),
        ];
    }
}
