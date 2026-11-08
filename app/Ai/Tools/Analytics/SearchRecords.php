<?php

declare(strict_types=1);

namespace App\Ai\Tools\Analytics;

use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\Billing;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Estimate;
use App\Domains\Sales\Models\Invoice;
use App\Domains\Sales\Models\Lead;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class SearchRecords implements Tool
{
    use InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search across projects, clients, leads, suppliers, invoices, estimates, purchase orders, billings and tasks by name, number or email. Use this first when you need a UUID for another tool.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $request->validate([
            'term' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        return $this->attempt(function () use ($request): array {
            $term = '%'.trim((string) $request['term']).'%';
            $perType = $this->limit($request['limit'] ?? null, 5, 20);

            $results = [];

            $results['projects'] = Project::query()
                ->where('company_id', $this->companyId())
                ->where(fn ($query) => $query->where('title', 'like', $term)->orWhere('project_number', 'like', $term))
                ->limit($perType)
                ->get()
                ->map(fn (Project $project) => [
                    'uuid' => $project->uuid,
                    'label' => $project->title,
                    'detail' => $project->project_number,
                ])->all();

            $results['clients'] = Client::query()
                ->where('company_id', $this->companyId())
                ->where(fn ($query) => $query->where('name', 'like', $term)->orWhere('company_name', 'like', $term)->orWhere('email', 'like', $term))
                ->limit($perType)
                ->get()
                ->map(fn (Client $client) => [
                    'uuid' => $client->uuid,
                    'label' => $client->name,
                    'detail' => $client->company_name ?? $client->email,
                ])->all();

            $results['leads'] = Lead::query()
                ->where('company_id', $this->companyId())
                ->where(fn ($query) => $query
                    ->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('email', 'like', $term))
                ->limit($perType)
                ->get()
                ->map(fn (Lead $lead) => [
                    'uuid' => $lead->uuid,
                    'label' => trim($lead->first_name.' '.$lead->last_name),
                    'detail' => $this->enumValue($lead->status),
                ])->all();

            $results['suppliers'] = Supplier::query()
                ->where('company_id', $this->companyId())
                ->where(fn ($query) => $query->where('name', 'like', $term)->orWhere('email', 'like', $term))
                ->limit($perType)
                ->get()
                ->map(fn (Supplier $supplier) => [
                    'uuid' => $supplier->uuid,
                    'label' => $supplier->name,
                    'detail' => $supplier->email,
                ])->all();

            $results['invoices'] = Invoice::query()
                ->where('company_id', $this->companyId())
                ->where('invoice_number', 'like', $term)
                ->limit($perType)
                ->get()
                ->map(fn (Invoice $invoice) => [
                    'uuid' => $invoice->uuid,
                    'label' => $invoice->invoice_number,
                    'detail' => $this->enumValue($invoice->status).' · '.$this->toDecimal($invoice->total),
                ])->all();

            $results['estimates'] = Estimate::query()
                ->where('company_id', $this->companyId())
                ->where(fn ($query) => $query->where('title', 'like', $term)->orWhere('estimate_number', 'like', $term))
                ->limit($perType)
                ->get()
                ->map(fn (Estimate $estimate) => [
                    'uuid' => $estimate->uuid,
                    'label' => $estimate->title,
                    'detail' => $estimate->estimate_number,
                ])->all();

            $results['purchase_orders'] = PurchaseOrder::query()
                ->where('company_id', $this->companyId())
                ->where('po_number', 'like', $term)
                ->limit($perType)
                ->get()
                ->map(fn (PurchaseOrder $order) => [
                    'uuid' => $order->uuid,
                    'label' => $order->po_number,
                    'detail' => $this->enumValue($order->status),
                ])->all();

            $results['billings'] = Billing::query()
                ->where('company_id', $this->companyId())
                ->where('billing_number', 'like', $term)
                ->limit($perType)
                ->get()
                ->map(fn (Billing $billing) => [
                    'uuid' => $billing->uuid,
                    'label' => $billing->billing_number,
                    'detail' => $this->enumValue($billing->status),
                ])->all();

            $results['tasks'] = Task::query()
                ->where('company_id', $this->companyId())
                ->where('title', 'like', $term)
                ->limit($perType)
                ->get()
                ->map(fn (Task $task) => [
                    'uuid' => $task->uuid,
                    'label' => $task->title,
                    'detail' => $this->enumValue($task->status),
                ])->all();

            return array_filter($results, fn (array $matches) => $matches !== []);
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'term' => $schema->string()->description('Name, number or email fragment to search for.')->required(),
            'limit' => $schema->integer()->description('Maximum matches per record type (default 5, max 20).'),
        ];
    }
}
