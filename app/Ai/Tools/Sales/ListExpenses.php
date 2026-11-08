<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListExpenses implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List project expenses with category, amount and date. Filter by project, category or date range.';
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
            $this->authorize('viewAny', Expense::class);

            $query = Expense::query()
                ->where('company_id', $this->companyId())
                ->when(filled($request['category'] ?? null), fn ($builder) => $builder->where('category', $request['category']))
                ->when(filled($validated['date_from'] ?? null), fn ($builder) => $builder->whereDate('date', '>=', $validated['date_from']))
                ->when(filled($validated['date_to'] ?? null), fn ($builder) => $builder->whereDate('date', '<=', $validated['date_to']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $expenses = $query->orderByDesc('date')->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $expenses->count(),
                'total' => $this->toDecimal((int) $expenses->sum('amount')),
                'expenses' => $expenses->map(fn (Expense $expense) => $this->expenseSummary($expense))->all(),
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
            'category' => $schema->string()->enum(array_column(ExpenseCategory::cases(), 'value'))->description('Optional category filter.'),
            'date_from' => $schema->string()->description('Optional start date (YYYY-MM-DD).'),
            'date_to' => $schema->string()->description('Optional end date (YYYY-MM-DD).'),
            'limit' => $schema->integer()->description('Maximum expenses to return (default 25, max 100).'),
        ];
    }
}
