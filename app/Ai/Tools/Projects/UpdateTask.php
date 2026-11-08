<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Actions\UpdateTaskAction;
use App\Domains\ProjectManagement\DTOs\TaskData;
use App\Domains\ProjectManagement\Enums\TaskPriority;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateTask implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a task: title, description, status, priority, assignee or due date. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'task' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'assignee' => ['nullable', 'string'],
            'due_date' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $task = $this->findRecord(Task::class, $validated['task'], 'task');
            $this->authorize('update', $task);

            $status = $task->status;
            $priority = $task->priority;

            try {
                if (filled($validated['status'] ?? null)) {
                    $status = TaskStatus::from((string) $validated['status']);
                }

                if (filled($validated['priority'] ?? null)) {
                    $priority = TaskPriority::from((string) $validated['priority']);
                }
            } catch (Throwable) {
                throw new ToolFailure(
                    'Invalid status or priority. Statuses: '.implode(', ', array_column(TaskStatus::cases(), 'value'))
                    .'. Priorities: '.implode(', ', array_column(TaskPriority::cases(), 'value')).'.'
                );
            }

            $assignedUserId = $task->assigned_user_id;

            if (array_key_exists('assignee', $validated) && filled($validated['assignee'])) {
                $assignedUserId = $this->findTeamMember($validated['assignee'])->id;
            }

            $updated = app(UpdateTaskAction::class)->execute($task, new TaskData(
                title: (string) ($validated['title'] ?? $task->title),
                projectId: $task->project_id,
                status: $status,
                priority: $priority,
                description: $validated['description'] ?? $task->description,
                assignedUserId: $assignedUserId,
                dueDate: filled($validated['due_date'] ?? null)
                    ? $this->parseDate($validated['due_date'])
                    : $task->due_date,
            ));

            return [
                'message' => 'Task updated.',
                'task' => $this->taskSummary($updated->fresh(['project', 'assignee'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'task' => $schema->string()->description('Task UUID.')->required(),
            'title' => $schema->string()->description('New title.'),
            'description' => $schema->string()->description('New description.'),
            'status' => $schema->string()->enum(array_column(TaskStatus::cases(), 'value'))->description('New status.'),
            'priority' => $schema->string()->enum(array_column(TaskPriority::cases(), 'value'))->description('New priority.'),
            'assignee' => $schema->string()->description('New assignee email, name or user id.'),
            'due_date' => $schema->string()->description('New due date (YYYY-MM-DD).'),
        ];
    }
}
