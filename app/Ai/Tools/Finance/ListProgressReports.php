<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListProgressReports implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List progress reports with report number, date, status and accomplished value. Filter by project or status.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', ProgressReport::class);

            $query = ProgressReport::query()
                ->with('items')
                ->where('company_id', $this->companyId())
                ->when(filled($request['status'] ?? null), fn ($builder) => $builder->where('status', $request['status']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $reports = $query->orderByDesc('report_date')->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $reports->count(),
                'progress_reports' => $reports->map(fn (ProgressReport $report) => $this->progressReportSummary($report))->all(),
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
            'status' => $schema->string()->enum(array_column(ProgressReportStatus::cases(), 'value'))->description('Optional status filter.'),
            'limit' => $schema->integer()->description('Maximum reports to return (default 25, max 100).'),
        ];
    }
}
