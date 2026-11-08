<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Actions\DeleteBoqItemAction;
use App\Domains\Finance\Models\BoqItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteBoqItem implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a bill of quantities line. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a BOQ line changes the priced scope of works.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'boq_item' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $item = $this->findRecord(BoqItem::class, $validated['boq_item'], 'BOQ item', ['item_code']);
            $this->authorize('delete', $item);

            $summary = $this->boqItemSummary($item->load('costCode'));

            app(DeleteBoqItemAction::class)->execute($item);

            return [
                'message' => 'BOQ item deleted.',
                'item' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'boq_item' => $schema->string()->description('BOQ item UUID or item code.')->required(),
        ];
    }
}
