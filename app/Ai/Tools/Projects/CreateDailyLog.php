<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\CreateDailyLogAction;
use App\Domains\ProjectManagement\DTOs\DailyLogData;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class CreateDailyLog implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Record a site daily log for a project, including weather, crew size, labour hours, equipment used, work performed, issues and safety incidents.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'log_date' => ['required', 'string'],
            'work_performed' => ['required', 'string', 'max:4000'],
            'weather' => ['nullable', 'string', 'max:100'],
            'crew_count' => ['nullable', 'integer', 'min:0'],
            'labor_hours' => ['nullable', 'numeric', 'min:0'],
            'equipment_usage' => ['nullable', 'string', 'max:1000'],
            'issues' => ['nullable', 'string', 'max:2000'],
            'safety_incidents' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', DailyLog::class);

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $log = app(CreateDailyLogAction::class)->execute(
                new DailyLogData(
                    projectId: $project->id,
                    logDate: $this->parseDate($validated['log_date']),
                    workPerformed: $validated['work_performed'],
                    weather: $validated['weather'] ?? null,
                    crewCount: (int) ($validated['crew_count'] ?? 0),
                    laborHours: (float) ($validated['labor_hours'] ?? 0),
                    equipmentUsage: $validated['equipment_usage'] ?? null,
                    issues: $validated['issues'] ?? null,
                    safetyIncidents: $validated['safety_incidents'] ?? null,
                ),
                $this->companyId(),
                $this->user->id,
            );

            return [
                'message' => 'Daily log recorded.',
                'log' => $this->dailyLogSummary($log->fresh(['project', 'author'])),
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
            'log_date' => $schema->string()->description('Log date (YYYY-MM-DD).')->required(),
            'work_performed' => $schema->string()->description('Description of work completed.')->required(),
            'weather' => $schema->string()->description('Weather conditions.'),
            'crew_count' => $schema->integer()->description('Number of workers on site.')->min(0),
            'labor_hours' => $schema->number()->description('Total labour hours.')->min(0),
            'equipment_usage' => $schema->string()->description('Equipment used on site.'),
            'issues' => $schema->string()->description('Issues or delays encountered.'),
            'safety_incidents' => $schema->string()->description('Safety incidents, if any.'),
        ];
    }
}
