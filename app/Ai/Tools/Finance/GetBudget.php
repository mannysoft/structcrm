<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\Budget;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetBudget implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get one budget with its line items, forecast and total so it can be reviewed or updated.';
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
            $this->authorize('view', $budget);

            return [
                'budget' => $this->budgetSummary($budget->load('items')),
                'items' => $this->budgetItems($budget),
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
