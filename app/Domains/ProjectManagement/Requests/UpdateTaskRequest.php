<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

class UpdateTaskRequest extends StoreTaskRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $this->user()?->can('update', $task) ?? false;
    }
}
