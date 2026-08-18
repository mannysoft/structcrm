<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\Models\Task;
use Illuminate\Support\Facades\DB;

final class DeleteTaskAction
{
    public function execute(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $task->delete();
        });
    }
}
