<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Services\ProjectProfitabilityService;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetProject implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get full detail for one project: profile, task status counts, contract value, approved change orders, expenses, billing totals and profitability. Accepts the project UUID or project number.';
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

            $project->load(['client', 'manager']);

            $tasks = Task::query()->where('project_id', $project->id)->get();

            $billing = Billing::query()->where('project_id', $project->id)->with('payments')->get();

            $profitability = app(ProjectProfitabilityService::class)->forProject($project);

            return [
                'project' => $this->projectSummary($project),
                'tasks' => [
                    'total' => $tasks->count(),
                    'by_status' => $tasks
                        ->groupBy(fn (Task $task) => $this->enumValue($task->status))
                        ->map(fn ($group, $status) => ['status' => $status, 'count' => $group->count()])
                        ->values()
                        ->all(),
                ],
                'contract_value' => $this->toDecimal(
                    (int) Contract::query()->where('project_id', $project->id)->sum('contract_value_amount')
                ),
                'approved_change_orders' => $this->toDecimal((int) ChangeOrder::query()
                    ->where('project_id', $project->id)
                    ->where('status', ChangeOrderStatus::Approved->value)
                    ->sum('amount')),
                'expenses_total' => $this->toDecimal((int) Expense::query()->where('project_id', $project->id)->sum('amount')),
                'billing' => [
                    'count' => $billing->count(),
                    'net' => $this->toDecimal((int) $billing->sum('net_amount')),
                    'collected' => $this->toDecimal((int) $billing->sum(fn (Billing $item) => $item->payments->sum('amount'))),
                ],
                'profitability' => [
                    'revised_contract_value' => $this->toDecimal($profitability->revisedContractValue),
                    'revised_budget' => $this->toDecimal($profitability->revisedBudget),
                    'actual_cost' => $this->toDecimal($profitability->actualCost),
                    'projected_profit' => $this->toDecimal($profitability->projectedProfit),
                    'projected_margin_percent' => $profitability->projectedMargin,
                    'cost_variance' => $this->toDecimal($profitability->costVariance),
                ],
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
