<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\TaskData;
use App\Domains\ProjectManagement\Models\Task;
use Illuminate\Support\Facades\DB;

final class UpdateTaskAction
{
    public function execute(Task $task, TaskData $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $task->update([
                'project_id' => $data->projectId,
                'title' => $data->title,
                'description' => $data->description,
                'status' => $data->status,
                'priority' => $data->priority,
                'assigned_user_id' => $data->assignedUserId,
                'due_date' => $data->dueDate,
            ]);

            return $task;
        });
    }
}
