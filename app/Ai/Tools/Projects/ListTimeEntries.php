<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\TimeEntry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListTimeEntries implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List time entries (clock in/out, break minutes, notes) filtered by project, team member or date range.';
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
            $this->authorize('viewAny', TimeEntry::class);

            $query = TimeEntry::query()
                ->with(['project', 'user'])
                ->where('company_id', $this->companyId())
                ->when(filled($validated['date_from'] ?? null), fn ($builder) => $builder->whereDate('clock_in_at', '>=', $validated['date_from']))
                ->when(filled($validated['date_to'] ?? null), fn ($builder) => $builder->whereDate('clock_in_at', '<=', $validated['date_to']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            if (filled($request['member'] ?? null)) {
                $query->where('user_id', $this->findTeamMember((string) $request['member'])->id);
            }

            $entries = $query->orderByDesc('clock_in_at')->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $entries->count(),
                'total_break_minutes' => (int) $entries->sum('break_minutes'),
                'entries' => $entries->map(fn (TimeEntry $entry) => $this->timeEntrySummary($entry))->all(),
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
            'member' => $schema->string()->description('Optional team member email, name or user id.'),
            'date_from' => $schema->string()->description('Optional start date (YYYY-MM-DD).'),
            'date_to' => $schema->string()->description('Optional end date (YYYY-MM-DD).'),
            'limit' => $schema->integer()->description('Maximum entries to return (default 25, max 100).'),
        ];
    }
}
