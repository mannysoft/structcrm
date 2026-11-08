<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateChangeOrderAction;
use App\Domains\Sales\DTOs\ChangeOrderData;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class CreateChangeOrder implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Raise a change order against a project. Amounts are in major currency units; approving it later revises the project budget.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'description' => ['required', 'string', 'max:2000'],
            'amount' => ['required', 'numeric'],
            'items' => ['nullable', 'array'],
            'items.*.category' => ['required_with:items', 'string'],
            'items.*.description' => ['required_with:items', 'string', 'max:500'],
            'items.*.amount' => ['required_with:items', 'numeric'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', ChangeOrder::class);

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $items = collect($validated['items'] ?? [])->map(fn (array $item) => [
                'category' => $item['category'],
                'description' => $item['description'],
                'amount' => $this->toCents($item['amount']),
            ])->all();

            $changeOrder = app(CreateChangeOrderAction::class)->execute(
                new ChangeOrderData(
                    projectId: $project->id,
                    description: $validated['description'],
                    amountInCents: $this->toCents($validated['amount']),
                    status: ChangeOrderStatus::Pending,
                    amountCurrency: $this->currency(),
                    items: $items,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Change order raised and awaiting decision.',
                'change_order' => $this->changeOrderSummary($changeOrder),
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
            'description' => $schema->string()->description('What the change covers.')->required(),
            'amount' => $schema->number()->description('Total amount in major currency units.')->required(),
            'items' => $schema->array()->items($schema->object([
                'category' => $schema->string()->enum(['material', 'labor', 'equipment', 'overhead', 'profit', 'other'])->required(),
                'description' => $schema->string()->required(),
                'amount' => $schema->number()->description('Item amount in major currency units.')->required(),
            ]))->description('Optional itemised breakdown.'),
        ];
    }
}
