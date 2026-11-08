<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\Billing;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetBilling implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get one billing with its line items and payment history, including the outstanding balance and collection status.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $request->validate([
            'billing' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($request): array {
            $billing = $this->findRecord(Billing::class, $request['billing'], 'billing', ['billing_number']);
            $this->authorize('view', $billing);

            $billing->load(['items', 'payments', 'project', 'progressReport']);

            return [
                'billing' => $this->billingSummary($billing),
                'project' => $billing->project?->title,
                'progress_report' => $billing->progressReport?->report_number,
                'items' => $billing->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => $this->toDecimal($item->unit_price),
                    'total' => $this->toDecimal($item->total),
                ])->all(),
                'payments' => $billing->payments->map(fn ($payment) => [
                    'date' => $payment->payment_date?->toDateString(),
                    'amount' => $this->toDecimal($payment->amount),
                    'method' => $payment->method,
                    'reference' => $payment->reference,
                    'notes' => $payment->notes,
                ])->all(),
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
        ];
    }
}
