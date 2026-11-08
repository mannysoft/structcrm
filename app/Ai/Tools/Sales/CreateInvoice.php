<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateInvoiceAction;
use App\Domains\Sales\DTOs\InvoiceData;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateInvoice implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create an invoice for a project with line items. Unit prices are in major currency units. The invoice number and total are generated from the items.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'issue_date' => ['nullable', 'string'],
            'due_date' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Invoice::class);

            try {
                $status = filled($validated['status'] ?? null)
                    ? InvoiceStatus::from($validated['status'])
                    : InvoiceStatus::Draft;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported invoice status. Use one of: '.implode(', ', array_column(InvoiceStatus::cases(), 'value')).'.');
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $issueDate = filled($validated['issue_date'] ?? null)
                ? $this->parseDate($validated['issue_date'])
                : $this->parseDate(today()->toDateString());

            $dueDate = filled($validated['due_date'] ?? null)
                ? $this->parseDate($validated['due_date'])
                : $issueDate->addDays(30);

            $items = collect($validated['items'])->map(fn (array $item) => [
                'description' => $item['description'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => $this->toCents($item['unit_price']),
            ])->all();

            $invoice = app(CreateInvoiceAction::class)->execute(
                new InvoiceData(
                    projectId: $project->id,
                    status: $status,
                    issueDate: $issueDate,
                    dueDate: $dueDate,
                    items: $items,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Invoice created.',
                'invoice' => $this->invoiceSummary($invoice->loadCount('items')),
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
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line item description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
            ]))->description('Invoice line items.')->required(),
            'status' => $schema->string()->enum(array_column(InvoiceStatus::cases(), 'value'))->description('Defaults to draft.'),
            'issue_date' => $schema->string()->description('Issue date (YYYY-MM-DD). Defaults to today.'),
            'due_date' => $schema->string()->description('Due date (YYYY-MM-DD). Defaults to 30 days after issue.'),
        ];
    }
}
