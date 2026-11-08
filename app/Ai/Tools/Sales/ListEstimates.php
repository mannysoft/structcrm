<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\Models\Estimate;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListEstimates implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List estimates with number, title, status, amount and validity. Filter by status, project or a title/number search.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Estimate::class);

            $search = trim((string) ($request['search'] ?? ''));

            $query = Estimate::query()
                ->withCount('items')
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where(
                    fn ($inner) => $inner->where('title', 'like', "%{$search}%")->orWhere('estimate_number', 'like', "%{$search}%")
                ))
                ->when(filled($request['status'] ?? null), fn ($builder) => $builder->where('status', $request['status']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $estimates = $query->latest()->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $estimates->count(),
                'estimates' => $estimates->map(fn (Estimate $estimate) => $this->estimateSummary($estimate))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional title or estimate number fragment.'),
            'status' => $schema->string()->enum(array_column(EstimateStatus::cases(), 'value'))->description('Optional status filter.'),
            'project' => $schema->string()->description('Optional project UUID or project number.'),
            'limit' => $schema->integer()->description('Maximum estimates to return (default 25, max 100).'),
        ];
    }
}
