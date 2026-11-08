<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\UpdateTimeEntryAction;
use App\Domains\ProjectManagement\DTOs\TimeEntryData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\TimeEntry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UpdateTimeEntry implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a time entry: project, clock-in, clock-out, break minutes or notes. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'entry' => ['required', 'string'],
            'project' => ['nullable', 'string'],
            'clock_in_at' => ['nullable', 'string'],
            'clock_out_at' => ['nullable', 'string'],
            'break_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $entry = $this->findRecord(TimeEntry::class, $validated['entry'], 'time entry');
            $this->authorize('update', $entry);

            $projectId = filled($validated['project'] ?? null)
                ? $this->findRecord(Project::class, $validated['project'], 'project', ['project_number'])->id
                : $entry->project_id;

            $updated = app(UpdateTimeEntryAction::class)->execute($entry, new TimeEntryData(
                projectId: $projectId,
                clockInAt: filled($validated['clock_in_at'] ?? null)
                    ? $this->parseDate($validated['clock_in_at'])
                    : $entry->clock_in_at,
                clockOutAt: filled($validated['clock_out_at'] ?? null)
                    ? $this->parseDate($validated['clock_out_at'])
                    : $entry->clock_out_at,
                breakMinutes: isset($validated['break_minutes']) ? (int) $validated['break_minutes'] : (int) $entry->break_minutes,
                notes: $validated['notes'] ?? $entry->notes,
                latitude: $entry->latitude,
                longitude: $entry->longitude,
            ));

            return [
                'message' => 'Time entry updated.',
                'entry' => $this->timeEntrySummary($updated->fresh(['project', 'user'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'entry' => $schema->string()->description('Time entry UUID.')->required(),
            'project' => $schema->string()->description('New project UUID or project number.'),
            'clock_in_at' => $schema->string()->description('New clock-in timestamp (YYYY-MM-DD HH:MM).'),
            'clock_out_at' => $schema->string()->description('New clock-out timestamp (YYYY-MM-DD HH:MM).'),
            'break_minutes' => $schema->integer()->description('New break duration in minutes.')->min(0),
            'notes' => $schema->string()->description('New notes.'),
        ];
    }
}
