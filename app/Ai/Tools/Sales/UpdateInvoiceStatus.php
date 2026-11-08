<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateInvoiceStatus implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Change an invoice status: draft, sent, partial, paid or overdue.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'invoice' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $invoice = $this->findRecord(Invoice::class, $validated['invoice'], 'invoice', ['invoice_number']);
            $this->authorize('update', $invoice);

            try {
                $status = InvoiceStatus::from($validated['status']);
            } catch (Throwable) {
                throw new ToolFailure('Unsupported invoice status. Use one of: '.implode(', ', array_column(InvoiceStatus::cases(), 'value')).'.');
            }

            $invoice->update(['status' => $status]);

            return [
                'message' => "Invoice {$invoice->invoice_number} marked as {$status->value}.",
                'invoice' => $this->invoiceSummary($invoice->fresh()->loadCount('items')),
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
            'status' => $schema->string()
                ->enum(array_column(InvoiceStatus::cases(), 'value'))
                ->description('New status.')
                ->required(),
        ];
    }
}
