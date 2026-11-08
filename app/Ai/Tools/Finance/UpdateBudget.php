<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\UpdateBudgetAction;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\Budget;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateBudget implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a budget: type, status or forecast amount. Supply items to replace the existing line items; omit items to keep them.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'budget' => ['required', 'string'],
            'type' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'forecast' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.cost_code' => ['nullable', 'string'],
            'items.*.boq_item' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $budget = $this->findRecord(Budget::class, $validated['budget'], 'budget');
            $this->authorize('update', $budget);

            try {
                $type = filled($validated['type'] ?? null) ? BudgetType::from($validated['type']) : $budget->type;
                $status = filled($validated['status'] ?? null) ? BudgetStatus::from($validated['status']) : $budget->status;
            } catch (Throwable) {
                throw new ToolFailure(
                    'Invalid budget type or status. Types: '.implode(', ', array_column(BudgetType::cases(), 'value'))
                    .'. Statuses: '.implode(', ', array_column(BudgetStatus::cases(), 'value')).'.'
                );
            }

            $items = filled($validated['items'] ?? null)
                ? collect($validated['items'])->map(fn (array $item) => [
                    'description' => $item['description'],
                    'quantity' => (float) $item['quantity'],
                    'unit' => $item['unit'] ?? 'lot',
                    'unit_price' => $this->toCents($item['unit_price']),
                    'cost_code_id' => filled($item['cost_code'] ?? null)
                        ? $this->findRecord(CostCode::class, $item['cost_code'], 'cost code', ['code'])->id
                        : null,
                    'boq_item_id' => filled($item['boq_item'] ?? null)
                        ? $this->findRecord(BoqItem::class, $item['boq_item'], 'BOQ item', ['item_code'])->id
                        : null,
                ])->all()
                : $budget->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'cost_code_id' => $item->cost_code_id,
                    'boq_item_id' => $item->boq_item_id,
                ])->all();

            $forecast = $budget->forecast_amount === null
                ? null
                : new Money((int) $budget->forecast_amount, $budget->currency);

            if (isset($validated['forecast'])) {
                $forecast = new Money($this->toCents($validated['forecast']), $this->currency());
            }

            $updated = app(UpdateBudgetAction::class)->execute($budget, new BudgetData(
                projectId: $budget->project_id,
                type: $type,
                status: $status,
                forecast: $forecast,
                items: $items,
            ));

            return [
                'message' => 'Budget updated.',
                'budget' => $this->budgetSummary($updated->load('items')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'budget' => $schema->string()->description('Budget UUID.')->required(),
            'type' => $schema->string()->enum(array_column(BudgetType::cases(), 'value'))->description('New type.'),
            'status' => $schema->string()->enum(array_column(BudgetStatus::cases(), 'value'))->description('New status.'),
            'forecast' => $schema->number()->description('New forecast amount in major currency units.')->min(0),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
                'unit' => $schema->string()->description('Unit of measure (defaults to lot).'),
                'cost_code' => $schema->string()->description('Optional cost code UUID or code.'),
                'boq_item' => $schema->string()->description('Optional BOQ item UUID or item code.'),
            ]))->description('Replacement line items. Omit to keep the current items.'),
        ];
    }
}
