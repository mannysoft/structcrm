<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListTasks implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List tasks, optionally filtered by project, status, priority, assignee or a title search. Returns due dates and assignees.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Task::class);

            $search = trim((string) ($request['search'] ?? ''));
            $projectIdentifier = $request['project'] ?? null;
            $assignee = $request['assignee'] ?? null;

            $query = Task::query()
                ->with(['project', 'assignee'])
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where('title', 'like', "%{$search}%"))
                ->when(filled($request['status'] ?? null), fn ($builder) => $builder->where('status', $request['status']))
                ->when(filled($request['priority'] ?? null), fn ($builder) => $builder->where('priority', $request['priority']))
                ->when(filled($request['overdue'] ?? null), fn ($builder) => $builder
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', today()));

            if (filled($projectIdentifier)) {
                $project = $this->findRecord(Project::class, (string) $projectIdentifier, 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            if (filled($assignee)) {
                $query->where('assigned_user_id', $this->findTeamMember((string) $assignee)->id);
            }

            $tasks = $query->orderBy('due_date')->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $tasks->count(),
                'tasks' => $tasks->map(fn (Task $task) => $this->taskSummary($task))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional title fragment.'),
            'project' => $schema->string()->description('Optional project UUID or project number.'),
            'status' => $schema->string()->enum(array_column(TaskStatus::cases(), 'value'))->description('Optional status filter.'),
            'priority' => $schema->string()->enum(['low', 'medium', 'high', 'urgent'])->description('Optional priority filter.'),
            'assignee' => $schema->string()->description('Optional assignee email, name or user id.'),
            'overdue' => $schema->boolean()->description('When true, only tasks past their due date are returned.'),
            'limit' => $schema->integer()->description('Maximum tasks to return (default 25, max 100).'),
        ];
    }
}
