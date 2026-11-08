<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetChangeOrder implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get one change order with its line items, amount, status and decision date.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'change_order' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $changeOrder = $this->findRecord(ChangeOrder::class, $validated['change_order'], 'change order', ['co_number']);
            $this->authorize('view', $changeOrder);

            return [
                'change_order' => $this->changeOrderSummary($changeOrder),
                'items' => $this->changeOrderItems($changeOrder->load('items')),
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
        ];
    }
}
