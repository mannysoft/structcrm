<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\CreateBudgetAction;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\Budget;
use App\Domains\Finance\Models\CostCode;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateBudget implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a budget version for a project (original or revised) with line items. The new budget becomes the current version and previous versions are archived.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.cost_code' => ['nullable', 'string'],
            'items.*.boq_item' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'forecast' => ['nullable', 'numeric', 'min:0'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Budget::class);

            try {
                $type = filled($validated['type'] ?? null) ? BudgetType::from($validated['type']) : BudgetType::Original;
                $status = filled($validated['status'] ?? null) ? BudgetStatus::from($validated['status']) : BudgetStatus::Draft;
            } catch (Throwable) {
                throw new ToolFailure(
                    'Invalid budget type or status. Types: '.implode(', ', array_column(BudgetType::cases(), 'value'))
                    .'. Statuses: '.implode(', ', array_column(BudgetStatus::cases(), 'value')).'.'
                );
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $items = collect($validated['items'])->map(function (array $item): array {
                return [
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
                ];
            })->all();

            $budget = app(CreateBudgetAction::class)->execute(
                new BudgetData(
                    projectId: $project->id,
                    type: $type,
                    status: $status,
                    forecast: filled($validated['forecast'] ?? null)
                        ? new Money($this->toCents($validated['forecast']), $this->currency())
                        : null,
                    items: $items,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Budget created.',
                'budget' => $this->budgetSummary($budget->load('items')),
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
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
                'unit' => $schema->string()->description('Unit of measure (defaults to lot).'),
                'cost_code' => $schema->string()->description('Optional cost code UUID or code.'),
                'boq_item' => $schema->string()->description('Optional BOQ item UUID or item code.'),
            ]))->description('Budget line items.')->required(),
            'type' => $schema->string()->enum(array_column(BudgetType::cases(), 'value'))->description('Defaults to original.'),
            'status' => $schema->string()->enum(array_column(BudgetStatus::cases(), 'value'))->description('Defaults to draft.'),
            'forecast' => $schema->number()->description('Optional forecast-to-complete amount in major currency units.'),
        ];
    }
}
