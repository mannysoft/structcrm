<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\UpdateExpenseAction;
use App\Domains\Sales\DTOs\ExpenseData;
use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateExpense implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a recorded expense: project, category, amount, date, notes or receipt path. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'expense' => ['required', 'string'],
            'project' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'date' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt_path' => ['nullable', 'string', 'max:2048'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $expense = $this->findRecord(Expense::class, $validated['expense'], 'expense');
            $this->authorize('update', $expense);

            try {
                $category = filled($validated['category'] ?? null)
                    ? ExpenseCategory::from($validated['category'])
                    : $expense->category;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported expense category. Use one of: '.implode(', ', array_column(ExpenseCategory::cases(), 'value')).'.');
            }

            $projectId = filled($validated['project'] ?? null)
                ? $this->findRecord(Project::class, $validated['project'], 'project', ['project_number'])->id
                : $expense->project_id;

            $updated = app(UpdateExpenseAction::class)->execute($expense, new ExpenseData(
                projectId: $projectId,
                category: $category,
                amount: isset($validated['amount'])
                    ? new Money($this->toCents($validated['amount']), $this->currency())
                    : $expense->expenseAmount,
                date: filled($validated['date'] ?? null)
                    ? $this->parseDate($validated['date'])
                    : $expense->date,
                receiptPath: $validated['receipt_path'] ?? $expense->receipt_path,
                notes: $validated['notes'] ?? $expense->notes,
            ));

            return [
                'message' => 'Expense updated.',
                'expense' => $this->expenseSummary($updated->fresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'expense' => $schema->string()->description('Expense UUID.')->required(),
            'project' => $schema->string()->description('New project UUID or project number.'),
            'category' => $schema->string()->enum(array_column(ExpenseCategory::cases(), 'value'))->description('New category.'),
            'amount' => $schema->number()->description('New amount in major currency units.')->min(0),
            'date' => $schema->string()->description('New expense date (YYYY-MM-DD).'),
            'notes' => $schema->string()->description('New notes.'),
            'receipt_path' => $schema->string()->description('New path of an already-uploaded receipt.'),
        ];
    }
}
