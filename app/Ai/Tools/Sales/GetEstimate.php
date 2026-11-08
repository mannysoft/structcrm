<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Models\Estimate;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetEstimate implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get one estimate with its line items, totals and validity so it can be reviewed or updated.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'estimate' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $estimate = $this->findRecord(Estimate::class, $validated['estimate'], 'estimate', ['estimate_number']);
            $this->authorize('view', $estimate);

            return [
                'estimate' => $this->estimateSummary($estimate->loadCount('items')),
                'items' => $this->estimateItems($estimate->load('items')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'estimate' => $schema->string()->description('Estimate UUID or estimate number.')->required(),
        ];
    }
}
