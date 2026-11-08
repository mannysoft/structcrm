<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Actions\DeleteCostCodeAction;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteCostCode implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a cost code. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a cost code removes a costing category used across projects.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'cost_code' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $costCode = $this->findRecord(CostCode::class, $validated['cost_code'], 'cost code', ['code']);
            $this->authorize('delete', $costCode);

            $summary = $this->costCodeSummary($costCode->load('parent'));

            app(DeleteCostCodeAction::class)->execute($costCode);

            return [
                'message' => 'Cost code deleted.',
                'cost_code' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'cost_code' => $schema->string()->description('Cost code UUID or code.')->required(),
        ];
    }
}
