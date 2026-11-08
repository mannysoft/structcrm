<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\UpdatePurchaseOrderAction;
use App\Domains\Sales\DTOs\PurchaseOrderData;
use App\Domains\Sales\Enums\PurchaseOrderStatus;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdatePurchaseOrder implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a purchase order: project, supplier or status. Supply items to replace the existing line items; omit items to keep them.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'purchase_order' => ['required', 'string'],
            'project' => ['nullable', 'string'],
            'supplier' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.description' => ['required_with:items', 'string', 'max:500'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $order = $this->findRecord(PurchaseOrder::class, $validated['purchase_order'], 'purchase order', ['po_number']);
            $this->authorize('update', $order);

            try {
                $status = filled($validated['status'] ?? null)
                    ? PurchaseOrderStatus::from($validated['status'])
                    : $order->status;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported purchase order status. Use one of: '.implode(', ', array_column(PurchaseOrderStatus::cases(), 'value')).'.');
            }

            $projectId = filled($validated['project'] ?? null)
                ? $this->findRecord(Project::class, $validated['project'], 'project', ['project_number'])->id
                : $order->project_id;

            $supplierId = filled($validated['supplier'] ?? null)
                ? $this->findRecord(Supplier::class, $validated['supplier'], 'supplier', ['name'])->id
                : $order->supplier_id;

            $items = filled($validated['items'] ?? null)
                ? collect($validated['items'])->map(fn (array $item) => [
                    'description' => $item['description'],
                    'quantity' => (float) $item['quantity'],
                    'unit_price' => $this->toCents($item['unit_price']),
                ])->all()
                : $order->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->all();

            $updated = app(UpdatePurchaseOrderAction::class)->execute($order, new PurchaseOrderData(
                projectId: $projectId,
                supplierId: $supplierId,
                status: $status,
                items: $items,
            ));

            return [
                'message' => 'Purchase order updated.',
                'purchase_order' => $this->purchaseOrderSummary($updated->load('supplier')->loadCount('items')),
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
            'project' => $schema->string()->description('New project UUID or project number.'),
            'supplier' => $schema->string()->description('New supplier UUID or exact name.'),
            'status' => $schema->string()->enum(array_column(PurchaseOrderStatus::cases(), 'value'))->description('New status.'),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line item description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
            ]))->description('Replacement line items. Omit to keep the current items.'),
        ];
    }
}
