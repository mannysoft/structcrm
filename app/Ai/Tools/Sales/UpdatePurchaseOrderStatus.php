<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Sales\Enums\PurchaseOrderStatus;
use App\Domains\Sales\Models\PurchaseOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdatePurchaseOrderStatus implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Change a purchase order status: draft, sent, approved or received.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'purchase_order' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $order = $this->findRecord(PurchaseOrder::class, $validated['purchase_order'], 'purchase order', ['po_number']);
            $this->authorize('update', $order);

            try {
                $status = PurchaseOrderStatus::from($validated['status']);
            } catch (Throwable) {
                throw new ToolFailure('Unsupported purchase order status. Use one of: '.implode(', ', array_column(PurchaseOrderStatus::cases(), 'value')).'.');
            }

            $order->update(['status' => $status]);

            return [
                'message' => "Purchase order {$order->po_number} marked as {$status->value}.",
                'purchase_order' => $this->purchaseOrderSummary($order->fresh(['supplier'])->loadCount('items')),
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
            'status' => $schema->string()
                ->enum(array_column(PurchaseOrderStatus::cases(), 'value'))
                ->description('New status.')
                ->required(),
        ];
    }
}
