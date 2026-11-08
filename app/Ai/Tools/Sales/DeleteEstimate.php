<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\DeleteEstimateAction;
use App\Domains\Sales\Models\Estimate;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteEstimate implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete an estimate and its line items. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting an estimate removes a priced offer from the sales pipeline.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'estimate' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $estimate = $this->findRecord(Estimate::class, $validated['estimate'], 'estimate', ['estimate_number']);
            $this->authorize('delete', $estimate);

            $summary = $this->estimateSummary($estimate->loadCount('items'));

            app(DeleteEstimateAction::class)->execute($estimate);

            return [
                'message' => 'Estimate deleted.',
                'estimate' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'estimate' => $schema->string()->description('Estimate UUID or estimate number.')->required(),
        ];
    }
}
