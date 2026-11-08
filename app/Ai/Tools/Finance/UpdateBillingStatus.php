<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateBillingStatus implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Change a billing status (draft, submitted, under_review, approved, certified, due, partially_paid, paid). Certifying stamps the certification date.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'billing' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $billing = $this->findRecord(Billing::class, $validated['billing'], 'billing', ['billing_number']);
            $this->authorize('update', $billing);

            try {
                $status = BillingStatus::from($validated['status']);
            } catch (Throwable) {
                throw new ToolFailure('Unsupported billing status. Use one of: '.implode(', ', array_column(BillingStatus::cases(), 'value')).'.');
            }

            $billing->update([
                'status' => $status,
                'certified_at' => $status->certifies() ? ($billing->certified_at ?? now()) : null,
            ]);

            return [
                'message' => "Billing {$billing->billing_number} marked as {$status->value}.",
                'billing' => $this->billingSummary($billing->fresh(['payments'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'billing' => $schema->string()->description('Billing UUID or billing number.')->required(),
            'status' => $schema->string()
                ->enum(array_column(BillingStatus::cases(), 'value'))
                ->description('New status.')
                ->required(),
        ];
    }
}
