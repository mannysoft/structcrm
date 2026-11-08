<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\Models\Estimate;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateEstimateStatus implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Move an estimate through its lifecycle: draft, sent, approved, rejected or converted.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'estimate' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $estimate = $this->findRecord(Estimate::class, $validated['estimate'], 'estimate', ['estimate_number', 'title']);
            $this->authorize('update', $estimate);

            try {
                $status = EstimateStatus::from($validated['status']);
            } catch (Throwable) {
                throw new ToolFailure('Unsupported estimate status. Use one of: '.implode(', ', array_column(EstimateStatus::cases(), 'value')).'.');
            }

            $estimate->update(['status' => $status]);

            return [
                'message' => "Estimate {$estimate->estimate_number} marked as {$status->value}.",
                'estimate' => $this->estimateSummary($estimate->fresh()->loadCount('items')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'estimate' => $schema->string()->description('Estimate UUID, estimate number or title.')->required(),
            'status' => $schema->string()
                ->enum(array_column(EstimateStatus::cases(), 'value'))
                ->description('New status.')
                ->required(),
        ];
    }
}
