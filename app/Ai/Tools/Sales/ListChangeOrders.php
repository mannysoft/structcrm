<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListChangeOrders implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List change orders with number, description, amount and approval status. Filter by status or project.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', ChangeOrder::class);

            $query = ChangeOrder::query()
                ->where('company_id', $this->companyId())
                ->when(filled($request['status'] ?? null), fn ($builder) => $builder->where('status', $request['status']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $changeOrders = $query->latest()->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $changeOrders->count(),
                'pending_total' => $this->toDecimal((int) $changeOrders
                    ->where('status', ChangeOrderStatus::Pending)
                    ->sum('amount')),
                'change_orders' => $changeOrders->map(fn (ChangeOrder $order) => $this->changeOrderSummary($order))->all(),
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
            'status' => $schema->string()->enum(array_column(ChangeOrderStatus::cases(), 'value'))->description('Optional status filter.'),
            'limit' => $schema->integer()->description('Maximum change orders to return (default 25, max 100).'),
        ];
    }
}
