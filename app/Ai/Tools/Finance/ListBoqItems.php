<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListBoqItems implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List bill-of-quantities items with quantity, unit, unit price and totals. Filter by project, cost code or description search.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', BoqItem::class);

            $search = trim((string) ($request['search'] ?? ''));

            $query = BoqItem::query()
                ->with(['costCode', 'project'])
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where(
                    fn ($inner) => $inner->where('description', 'like', "%{$search}%")->orWhere('item_code', 'like', "%{$search}%")
                ));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $items = $query->orderBy('item_code')->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $items->count(),
                'total' => $this->toDecimal((int) $items->sum('total')),
                'items' => $items->map(fn (BoqItem $item) => $this->boqItemSummary($item))->all(),
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
            'search' => $schema->string()->description('Optional item code or description fragment.'),
            'limit' => $schema->integer()->description('Maximum items to return (default 25, max 100).'),
        ];
    }
}
