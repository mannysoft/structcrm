<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListInvoices implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List invoices with number, status, total, issue and due dates. Filter by status, project or invoice number search.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Invoice::class);

            $search = trim((string) ($request['search'] ?? ''));

            $query = Invoice::query()
                ->withCount('items')
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where('invoice_number', 'like', "%{$search}%"))
                ->when(filled($request['status'] ?? null), fn ($builder) => $builder->where('status', $request['status']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $invoices = $query->latest()->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $invoices->count(),
                'invoices' => $invoices->map(fn (Invoice $invoice) => $this->invoiceSummary($invoice))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional invoice number fragment.'),
            'status' => $schema->string()->enum(array_column(InvoiceStatus::cases(), 'value'))->description('Optional status filter.'),
            'project' => $schema->string()->description('Optional project UUID or project number.'),
            'limit' => $schema->integer()->description('Maximum invoices to return (default 25, max 100).'),
        ];
    }
}
