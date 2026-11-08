<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListDailyLogs implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List site daily logs with date, crew count, labour hours, work performed, issues and safety incidents. Filter by project or date range.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return $this->attempt(function () use ($request, $validated): array {
            $this->authorize('viewAny', DailyLog::class);

            $query = DailyLog::query()
                ->with(['project', 'author'])
                ->where('company_id', $this->companyId())
                ->when(filled($validated['date_from'] ?? null), fn ($builder) => $builder->whereDate('log_date', '>=', $validated['date_from']))
                ->when(filled($validated['date_to'] ?? null), fn ($builder) => $builder->whereDate('log_date', '<=', $validated['date_to']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $logs = $query->orderByDesc('log_date')->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $logs->count(),
                'logs' => $logs->map(fn (DailyLog $log) => $this->dailyLogSummary($log))->all(),
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
            'date_from' => $schema->string()->description('Optional start date (YYYY-MM-DD).'),
            'date_to' => $schema->string()->description('Optional end date (YYYY-MM-DD).'),
            'limit' => $schema->integer()->description('Maximum logs to return (default 25, max 100).'),
        ];
    }
}
