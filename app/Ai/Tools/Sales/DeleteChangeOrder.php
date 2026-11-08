<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\DeleteChangeOrderAction;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteChangeOrder implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a change order and its line items. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a change order removes a contract variation record.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'change_order' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $changeOrder = $this->findRecord(ChangeOrder::class, $validated['change_order'], 'change order', ['co_number']);
            $this->authorize('delete', $changeOrder);

            $summary = $this->changeOrderSummary($changeOrder);

            app(DeleteChangeOrderAction::class)->execute($changeOrder);

            return [
                'message' => 'Change order deleted.',
                'change_order' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'change_order' => $schema->string()->description('Change order UUID or CO number.')->required(),
        ];
    }
}
