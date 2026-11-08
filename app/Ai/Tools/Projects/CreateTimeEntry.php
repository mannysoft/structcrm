<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\CreateTimeEntryAction;
use App\Domains\ProjectManagement\DTOs\TimeEntryData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\TimeEntry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class CreateTimeEntry implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Record a time entry for the current user on a project: clock in, optional clock out, break minutes and notes.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'clock_in_at' => ['required', 'string'],
            'clock_out_at' => ['nullable', 'string'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', TimeEntry::class);

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $entry = app(CreateTimeEntryAction::class)->execute(
                new TimeEntryData(
                    projectId: $project->id,
                    clockInAt: $this->parseDate($validated['clock_in_at']),
                    clockOutAt: filled($validated['clock_out_at'] ?? null)
                        ? $this->parseDate($validated['clock_out_at'])
                        : null,
                    breakMinutes: (int) ($validated['break_minutes'] ?? 0),
                    notes: $validated['notes'] ?? null,
                ),
                $this->companyId(),
                $this->user->id,
            );

            return [
                'message' => 'Time entry recorded.',
                'entry' => $this->timeEntrySummary($entry->fresh(['project', 'user'])),
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
            'clock_in_at' => $schema->string()->description('Clock-in timestamp (YYYY-MM-DD HH:MM).')->required(),
            'clock_out_at' => $schema->string()->description('Optional clock-out timestamp (YYYY-MM-DD HH:MM).'),
            'break_minutes' => $schema->integer()->description('Break duration in minutes.')->min(0),
            'notes' => $schema->string()->description('Optional notes.'),
        ];
    }
}
