<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Actions\CreateTaskAction;
use App\Domains\ProjectManagement\DTOs\TaskData;
use App\Domains\ProjectManagement\Enums\TaskPriority;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateTask implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a task on a project with an optional assignee, priority and due date.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'project' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'assignee' => ['nullable', 'string'],
            'due_date' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Task::class);

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $task = app(CreateTaskAction::class)->execute(
                new TaskData(
                    title: $validated['title'],
                    projectId: $project->id,
                    status: $this->parseStatus($validated['status'] ?? null),
                    priority: $this->parsePriority($validated['priority'] ?? null),
                    description: $validated['description'] ?? null,
                    assignedUserId: filled($validated['assignee'] ?? null)
                        ? $this->findTeamMember($validated['assignee'])->id
                        : null,
                    dueDate: filled($validated['due_date'] ?? null)
                        ? $this->parseDate($validated['due_date'])
                        : null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Task created.',
                'task' => $this->taskSummary($task->fresh(['project', 'assignee'])),
            ];
        });
    }

    private function parseStatus(?string $status): TaskStatus
    {
        if ($status === null || trim($status) === '') {
            return TaskStatus::ToDo;
        }

        try {
            return TaskStatus::from($status);
        } catch (Throwable) {
            throw new ToolFailure('Unsupported task status. Use one of: '.implode(', ', array_column(TaskStatus::cases(), 'value')).'.');
        }
    }

    private function parsePriority(?string $priority): TaskPriority
    {
        if ($priority === null || trim($priority) === '') {
            return TaskPriority::Medium;
        }

        try {
            return TaskPriority::from($priority);
        } catch (Throwable) {
            throw new ToolFailure('Unsupported task priority. Use one of: '.implode(', ', array_column(TaskPriority::cases(), 'value')).'.');
        }
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Task title.')->required(),
            'project' => $schema->string()->description('Project UUID or project number.')->required(),
            'description' => $schema->string()->description('Optional details.'),
            'status' => $schema->string()->enum(array_column(TaskStatus::cases(), 'value'))->description('Defaults to to_do.'),
            'priority' => $schema->string()->enum(array_column(TaskPriority::cases(), 'value'))->description('Defaults to medium.'),
            'assignee' => $schema->string()->description('Optional assignee email, name or user id.'),
            'due_date' => $schema->string()->description('Optional due date (YYYY-MM-DD).'),
        ];
    }
}
