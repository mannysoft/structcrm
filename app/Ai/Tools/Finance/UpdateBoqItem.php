<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\UpdateBoqItemAction;
use App\Domains\Finance\DTOs\BoqItemData;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UpdateBoqItem implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a bill of quantities line: description, quantity, unit, unit price, item code or cost code. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'boq_item' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'item_code' => ['nullable', 'string', 'max:50'],
            'cost_code' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $item = $this->findRecord(BoqItem::class, $validated['boq_item'], 'BOQ item', ['item_code']);
            $this->authorize('update', $item);

            $costCodeId = filled($validated['cost_code'] ?? null)
                ? $this->findRecord(CostCode::class, $validated['cost_code'], 'cost code', ['code'])->id
                : $item->cost_code_id;

            $updated = app(UpdateBoqItemAction::class)->execute($item, new BoqItemData(
                projectId: $item->project_id,
                description: (string) ($validated['description'] ?? $item->description),
                quantity: isset($validated['quantity']) ? (float) $validated['quantity'] : (float) $item->quantity,
                unit: (string) ($validated['unit'] ?? $item->unit),
                unitPrice: new Money($this->toCents($validated['unit_price'] ?? $this->toDecimal($item->unit_price)), $this->currency()),
                costCodeId: $costCodeId,
                itemCode: $validated['item_code'] ?? $item->item_code,
            ));

            return [
                'message' => 'BOQ item updated.',
                'item' => $this->boqItemSummary($updated->fresh(['costCode'])),
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
            'description' => $schema->string()->description('New description.'),
            'quantity' => $schema->number()->description('New quantity.')->min(0),
            'unit' => $schema->string()->description('New unit of measure.'),
            'unit_price' => $schema->number()->description('New unit price in major currency units.')->min(0),
            'item_code' => $schema->string()->description('New item code.'),
            'cost_code' => $schema->string()->description('New cost code UUID or code.'),
        ];
    }
}
