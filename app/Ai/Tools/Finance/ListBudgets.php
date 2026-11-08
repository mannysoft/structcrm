<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\Budget;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListBudgets implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List project budgets with type, version, status, total and forecast. Filter by project, type or current budgets only.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Budget::class);

            $query = Budget::query()
                ->with('items')
                ->where('company_id', $this->companyId())
                ->when(filled($request['type'] ?? null), fn ($builder) => $builder->where('type', $request['type']))
                ->when(filter_var($request['current_only'] ?? false, FILTER_VALIDATE_BOOLEAN), fn ($builder) => $builder->where('is_current', true));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $budgets = $query->orderByDesc('version')->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $budgets->count(),
                'budgets' => $budgets->map(fn (Budget $budget) => $this->budgetSummary($budget))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('Optional project UUID or project number.'),
            'type' => $schema->string()->enum(array_column(BudgetType::cases(), 'value'))->description('Optional budget type filter.'),
            'current_only' => $schema->boolean()->description('When true, only the current budget version is returned.'),
            'limit' => $schema->integer()->description('Maximum budgets to return (default 25, max 100).'),
        ];
    }
}
