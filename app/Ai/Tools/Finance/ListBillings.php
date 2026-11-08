<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListBillings implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List progress billings with period, gross, retention, net, amount paid, balance and collection status. Filter by project or status.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Billing::class);

            $query = Billing::query()
                ->with('payments')
                ->withCount('items')
                ->where('company_id', $this->companyId())
                ->when(filled($request['status'] ?? null), fn ($builder) => $builder->where('status', $request['status']))
                ->when(filter_var($request['overdue_only'] ?? false, FILTER_VALIDATE_BOOLEAN), fn ($builder) => $builder
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', today()));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $billings = $query->latest()->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $billings->count(),
                'net_total' => $this->toDecimal((int) $billings->sum('net_amount')),
                'outstanding_total' => $this->toDecimal((int) $billings->sum(fn (Billing $billing) => $billing->balanceAmount())),
                'billings' => $billings->map(fn (Billing $billing) => $this->billingSummary($billing))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('Optional project UUID or project number.'),
            'status' => $schema->string()->enum(array_column(BillingStatus::cases(), 'value'))->description('Optional status filter.'),
            'overdue_only' => $schema->boolean()->description('When true, only billings past their due date are returned.'),
            'limit' => $schema->integer()->description('Maximum billings to return (default 25, max 100).'),
        ];
    }
}
