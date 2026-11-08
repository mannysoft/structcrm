<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetInvoice implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get one invoice with its line items, totals and dates so it can be reviewed or updated.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'invoice' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $invoice = $this->findRecord(Invoice::class, $validated['invoice'], 'invoice', ['invoice_number']);
            $this->authorize('view', $invoice);

            return [
                'invoice' => $this->invoiceSummary($invoice->loadCount('items')),
                'items' => $this->invoiceItems($invoice->load('items')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice' => $schema->string()->description('Invoice UUID or invoice number.')->required(),
        ];
    }
}
