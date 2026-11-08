<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Actions\DeleteBudgetAction;
use App\Domains\Finance\Models\Budget;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteBudget implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a budget and its line items. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a budget removes an approved cost baseline.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'budget' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $budget = $this->findRecord(Budget::class, $validated['budget'], 'budget');
            $this->authorize('delete', $budget);

            $summary = $this->budgetSummary($budget->load('items'));

            app(DeleteBudgetAction::class)->execute($budget);

            return [
                'message' => 'Budget deleted.',
                'budget' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'budget' => $schema->string()->description('Budget UUID.')->required(),
        ];
    }
}
