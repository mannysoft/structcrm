<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\UpdateChangeOrderAction;
use App\Domains\Sales\DTOs\ChangeOrderData;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UpdateChangeOrder implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a change order description, amount or line items while it is still pending. Supply items to replace the existing breakdown; omit items to keep them. Decisions are made separately with the approve or reject tool.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'change_order' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['nullable', 'numeric'],
            'items' => ['nullable', 'array'],
            'items.*.category' => ['required_with:items', 'string'],
            'items.*.description' => ['required_with:items', 'string', 'max:500'],
            'items.*.amount' => ['required_with:items', 'numeric'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $changeOrder = $this->findRecord(ChangeOrder::class, $validated['change_order'], 'change order', ['co_number']);
            $this->authorize('update', $changeOrder);

            $items = filled($validated['items'] ?? null)
                ? collect($validated['items'])->map(fn (array $item) => [
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'amount' => $this->toCents($item['amount']),
                ])->all()
                : $changeOrder->items->map(fn ($item) => [
                    'category' => $this->enumValue($item->category),
                    'description' => $item->description,
                    'amount' => $item->amount,
                ])->all();

            $updated = app(UpdateChangeOrderAction::class)->execute($changeOrder, new ChangeOrderData(
                projectId: $changeOrder->project_id,
                description: (string) ($validated['description'] ?? $changeOrder->description),
                amountInCents: isset($validated['amount'])
                    ? $this->toCents($validated['amount'])
                    : $changeOrder->amount,
                status: $changeOrder->status,
                amountCurrency: $changeOrder->amount_currency,
                items: $items,
            ));

            return [
                'message' => 'Change order updated.',
                'change_order' => $this->changeOrderSummary($updated->fresh()),
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
            'description' => $schema->string()->description('New description.'),
            'amount' => $schema->number()->description('New total amount in major currency units.'),
            'items' => $schema->array()->items($schema->object([
                'category' => $schema->string()->description('Cost category, e.g. material, labour, equipment, overhead, profit, other.')->required(),
                'description' => $schema->string()->description('Line description.')->required(),
                'amount' => $schema->number()->description('Amount in major currency units.')->required(),
            ]))->description('Replacement line items. Omit to keep the current items.'),
        ];
    }
}
