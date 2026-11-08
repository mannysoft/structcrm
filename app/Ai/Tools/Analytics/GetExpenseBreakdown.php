<?php

declare(strict_types=1);

namespace App\Ai\Tools\Analytics;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetExpenseBreakdown implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Break down recorded expenses by category and by project for an optional date range, including totals and the biggest individual expenses.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return $this->attempt(function () use ($request, $validated): array {
            $from = $validated['date_from'] ?? null;
            $to = $validated['date_to'] ?? null;
            $projectUuid = $request['project'] ?? null;

            $query = Expense::query()
                ->where('company_id', $this->companyId())
                ->when($from, fn ($builder) => $builder->whereDate('date', '>=', $from))
                ->when($to, fn ($builder) => $builder->whereDate('date', '<=', $to));

            if (filled($projectUuid)) {
                $project = $this->findRecord(Project::class, (string) $projectUuid, 'project', ['project_number']);
                $this->authorize('view', $project);
                $query->where('project_id', $project->id);
            }

            $expenses = $query->with('project')->get();

            $byCategory = $expenses
                ->groupBy(fn (Expense $expense) => $this->enumValue($expense->category))
                ->map(fn ($group, $category) => [
                    'category' => $category,
                    'count' => $group->count(),
                    'total' => $this->toDecimal((int) $group->sum('amount')),
                ])
                ->sortByDesc('total')
                ->values()
                ->all();

            $byProject = $expenses
                ->groupBy(fn (Expense $expense) => $expense->project?->title ?? 'Unassigned')
                ->map(fn ($group, $projectTitle) => [
                    'project' => $projectTitle,
                    'project_uuid' => $group->first()?->project?->uuid,
                    'count' => $group->count(),
                    'total' => $this->toDecimal((int) $group->sum('amount')),
                ])
                ->sortByDesc('total')
                ->values()
                ->all();

            $largest = $expenses
                ->sortByDesc('amount')
                ->take(5)
                ->map(fn (Expense $expense) => $this->expenseSummary($expense))
                ->values()
                ->all();

            return [
                'currency' => $this->currency(),
                'date_from' => $from,
                'date_to' => $to,
                'total' => $this->toDecimal((int) $expenses->sum('amount')),
                'count' => $expenses->count(),
                'by_category' => $byCategory,
                'by_project' => $byProject,
                'largest_expenses' => $largest,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'date_from' => $schema->string()->description('Optional start date (YYYY-MM-DD).'),
            'date_to' => $schema->string()->description('Optional end date (YYYY-MM-DD).'),
            'project' => $schema->string()->description('Optional project UUID or project number to narrow the breakdown.'),
        ];
    }
}
