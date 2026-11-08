<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\DeleteInvoiceAction;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteInvoice implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete an invoice and its line items. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting an invoice removes a billing document from the receivables ledger.');
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
            $this->authorize('delete', $invoice);

            $summary = $this->invoiceSummary($invoice->loadCount('items'));

            app(DeleteInvoiceAction::class)->execute($invoice);

            return [
                'message' => 'Invoice deleted.',
                'invoice' => $summary,
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
