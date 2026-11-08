<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\UpdateInvoiceAction;
use App\Domains\Sales\DTOs\InvoiceData;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateInvoice implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update an invoice: project, status, issue date or due date. Supply items to replace the existing line items; omit items to keep them.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'invoice' => ['required', 'string'],
            'project' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'issue_date' => ['nullable', 'string'],
            'due_date' => ['nullable', 'string'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.description' => ['required_with:items', 'string', 'max:500'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $invoice = $this->findRecord(Invoice::class, $validated['invoice'], 'invoice', ['invoice_number']);
            $this->authorize('update', $invoice);

            try {
                $status = filled($validated['status'] ?? null)
                    ? InvoiceStatus::from($validated['status'])
                    : $invoice->status;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported invoice status. Use one of: '.implode(', ', array_column(InvoiceStatus::cases(), 'value')).'.');
            }

            $projectId = filled($validated['project'] ?? null)
                ? $this->findRecord(Project::class, $validated['project'], 'project', ['project_number'])->id
                : $invoice->project_id;

            $items = filled($validated['items'] ?? null)
                ? collect($validated['items'])->map(fn (array $item) => [
                    'description' => $item['description'],
                    'quantity' => (float) $item['quantity'],
                    'unit_price' => $this->toCents($item['unit_price']),
                ])->all()
                : $invoice->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->all();

            $updated = app(UpdateInvoiceAction::class)->execute($invoice, new InvoiceData(
                projectId: $projectId,
                status: $status,
                issueDate: filled($validated['issue_date'] ?? null)
                    ? $this->parseDate($validated['issue_date'])
                    : $invoice->issue_date,
                dueDate: filled($validated['due_date'] ?? null)
                    ? $this->parseDate($validated['due_date'])
                    : $invoice->due_date,
                items: $items,
            ));

            return [
                'message' => 'Invoice updated.',
                'invoice' => $this->invoiceSummary($updated->loadCount('items')),
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
            'project' => $schema->string()->description('New project UUID or project number.'),
            'status' => $schema->string()->enum(array_column(InvoiceStatus::cases(), 'value'))->description('New status.'),
            'issue_date' => $schema->string()->description('New issue date (YYYY-MM-DD).'),
            'due_date' => $schema->string()->description('New due date (YYYY-MM-DD).'),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line item description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
            ]))->description('Replacement line items. Omit to keep the current items.'),
        ];
    }
}
