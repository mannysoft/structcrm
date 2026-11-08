<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateExpenseAction;
use App\Domains\Sales\DTOs\ExpenseData;
use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateExpense implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Record a project expense (labour, fuel, materials, equipment or subcontract) with an amount and date.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'category' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt_path' => ['nullable', 'string', 'max:2048'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Expense::class);

            try {
                $category = ExpenseCategory::from($validated['category']);
            } catch (Throwable) {
                throw new ToolFailure('Unsupported expense category. Use one of: '.implode(', ', array_column(ExpenseCategory::cases(), 'value')).'.');
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $expense = app(CreateExpenseAction::class)->execute(
                new ExpenseData(
                    projectId: $project->id,
                    category: $category,
                    amount: new Money($this->toCents($validated['amount']), $this->currency()),
                    date: filled($validated['date'] ?? null)
                        ? $this->parseDate($validated['date'])
                        : $this->parseDate(today()->toDateString()),
                    receiptPath: $validated['receipt_path'] ?? null,
                    notes: $validated['notes'] ?? null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Expense recorded.',
                'expense' => $this->expenseSummary($expense->fresh()),
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
            'category' => $schema->string()->enum(array_column(ExpenseCategory::cases(), 'value'))->description('Expense category.')->required(),
            'amount' => $schema->number()->description('Amount in major currency units.')->required(),
            'date' => $schema->string()->description('Expense date (YYYY-MM-DD). Defaults to today.'),
            'notes' => $schema->string()->description('Optional notes.'),
            'receipt_path' => $schema->string()->description('Optional path of an already-uploaded receipt.'),
        ];
    }
}
