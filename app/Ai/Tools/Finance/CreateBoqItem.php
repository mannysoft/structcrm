<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\CreateBoqItemAction;
use App\Domains\Finance\DTOs\BoqItemData;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\CostCode;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class CreateBoqItem implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Add a bill-of-quantities line to a project with quantity, unit and unit price. The line total is calculated.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'item_code' => ['nullable', 'string', 'max:50'],
            'cost_code' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', BoqItem::class);

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $costCodeId = filled($validated['cost_code'] ?? null)
                ? $this->findRecord(CostCode::class, $validated['cost_code'], 'cost code', ['code'])->id
                : null;

            $item = app(CreateBoqItemAction::class)->execute(
                new BoqItemData(
                    projectId: $project->id,
                    description: $validated['description'],
                    quantity: (float) $validated['quantity'],
                    unit: $validated['unit'],
                    unitPrice: new Money($this->toCents($validated['unit_price']), $this->currency()),
                    costCodeId: $costCodeId,
                    itemCode: $validated['item_code'] ?? null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'BOQ item created.',
                'item' => $this->boqItemSummary($item->fresh(['costCode'])),
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
            'description' => $schema->string()->description('Line description.')->required(),
            'quantity' => $schema->number()->description('Quantity.')->required(),
            'unit' => $schema->string()->description('Unit of measure, e.g. m2, cu.m, lot.')->required(),
            'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
            'item_code' => $schema->string()->description('Optional item code.'),
            'cost_code' => $schema->string()->description('Optional cost code UUID or code.'),
        ];
    }
}
