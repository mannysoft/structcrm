<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreatePurchaseOrderAction;
use App\Domains\Sales\DTOs\PurchaseOrderData;
use App\Domains\Sales\Enums\PurchaseOrderStatus;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreatePurchaseOrder implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a purchase order for a project and supplier with line items. Unit prices are in major currency units.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'supplier' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', PurchaseOrder::class);

            try {
                $status = filled($validated['status'] ?? null)
                    ? PurchaseOrderStatus::from($validated['status'])
                    : PurchaseOrderStatus::Draft;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported purchase order status. Use one of: '.implode(', ', array_column(PurchaseOrderStatus::cases(), 'value')).'.');
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);
            $supplier = $this->findRecord(Supplier::class, $validated['supplier'], 'supplier', ['name']);

            $items = collect($validated['items'])->map(fn (array $item) => [
                'description' => $item['description'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => $this->toCents($item['unit_price']),
            ])->all();

            $order = app(CreatePurchaseOrderAction::class)->execute(
                new PurchaseOrderData(
                    projectId: $project->id,
                    supplierId: $supplier->id,
                    status: $status,
                    items: $items,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Purchase order created.',
                'purchase_order' => $this->purchaseOrderSummary($order->load(['supplier'])->loadCount('items')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('Project UUID or project number.')->required(),
            'supplier' => $schema->string()->description('Supplier UUID or name.')->required(),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Item description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
            ]))->description('Order line items.')->required(),
            'status' => $schema->string()->enum(array_column(PurchaseOrderStatus::cases(), 'value'))->description('Defaults to draft.'),
        ];
    }
}
