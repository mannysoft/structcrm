<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\DeletePurchaseOrderAction;
use App\Domains\Sales\Models\PurchaseOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeletePurchaseOrder implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a purchase order and its line items. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a purchase order removes a procurement commitment record.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'purchase_order' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $order = $this->findRecord(PurchaseOrder::class, $validated['purchase_order'], 'purchase order', ['po_number']);
            $this->authorize('delete', $order);

            $summary = $this->purchaseOrderSummary($order->load('supplier')->loadCount('items'));

            app(DeletePurchaseOrderAction::class)->execute($order);

            return [
                'message' => 'Purchase order deleted.',
                'purchase_order' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'purchase_order' => $schema->string()->description('Purchase order UUID or PO number.')->required(),
        ];
    }
}
