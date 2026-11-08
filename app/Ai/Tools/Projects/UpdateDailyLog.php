<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\UpdateDailyLogAction;
use App\Domains\ProjectManagement\DTOs\DailyLogData;
use App\Domains\ProjectManagement\Models\DailyLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UpdateDailyLog implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a daily site log: date, work performed, weather, crew count, labour hours, equipment usage, issues or safety incidents. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'log' => ['required', 'string'],
            'log_date' => ['nullable', 'string'],
            'work_performed' => ['nullable', 'string', 'max:2000'],
            'weather' => ['nullable', 'string', 'max:255'],
            'crew_count' => ['nullable', 'integer', 'min:0'],
            'labor_hours' => ['nullable', 'numeric', 'min:0'],
            'equipment_usage' => ['nullable', 'string', 'max:1000'],
            'issues' => ['nullable', 'string', 'max:2000'],
            'safety_incidents' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $log = $this->findRecord(DailyLog::class, $validated['log'], 'daily log');
            $this->authorize('update', $log);

            $updated = app(UpdateDailyLogAction::class)->execute($log, new DailyLogData(
                projectId: $log->project_id,
                logDate: filled($validated['log_date'] ?? null)
                    ? $this->parseDate($validated['log_date'])
                    : $log->log_date,
                workPerformed: (string) ($validated['work_performed'] ?? $log->work_performed),
                weather: $validated['weather'] ?? $log->weather,
                crewCount: isset($validated['crew_count']) ? (int) $validated['crew_count'] : (int) $log->crew_count,
                laborHours: isset($validated['labor_hours']) ? (float) $validated['labor_hours'] : (float) $log->labor_hours,
                equipmentUsage: $validated['equipment_usage'] ?? $log->equipment_usage,
                issues: $validated['issues'] ?? $log->issues,
                safetyIncidents: $validated['safety_incidents'] ?? $log->safety_incidents,
            ));

            return [
                'message' => 'Daily log updated.',
                'log' => $this->dailyLogSummary($updated->fresh(['project', 'author'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'log' => $schema->string()->description('Daily log UUID.')->required(),
            'log_date' => $schema->string()->description('New log date (YYYY-MM-DD).'),
            'work_performed' => $schema->string()->description('New description of work completed.'),
            'weather' => $schema->string()->description('New weather conditions.'),
            'crew_count' => $schema->integer()->description('New number of workers on site.')->min(0),
            'labor_hours' => $schema->number()->description('New total labour hours.')->min(0),
            'equipment_usage' => $schema->string()->description('New equipment used on site.'),
            'issues' => $schema->string()->description('New issues or delays.'),
            'safety_incidents' => $schema->string()->description('New safety incidents.'),
        ];
    }
}
