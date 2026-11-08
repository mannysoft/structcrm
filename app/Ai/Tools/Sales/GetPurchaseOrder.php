<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Models\PurchaseOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetPurchaseOrder implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get one purchase order with its supplier, line items and total so it can be reviewed or updated.';
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
            $this->authorize('view', $order);

            return [
                'purchase_order' => $this->purchaseOrderSummary($order->load('supplier')->loadCount('items')),
                'items' => $this->purchaseOrderItems($order->load('items')),
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
