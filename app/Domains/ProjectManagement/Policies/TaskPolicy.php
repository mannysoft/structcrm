<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Policies;

use App\Domains\ProjectManagement\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->company_id === $task->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasCapability('manage_tasks');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->company_id === $task->company_id && $user->hasCapability('manage_tasks');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->company_id === $task->company_id && $user->hasCapability('manage_tasks');
    }
}
