<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Policies;

use App\Domains\ProjectManagement\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }

    public function create(User $user): bool
    {
        // Add more granular role checks here in the future
        return $user->company_id !== null;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }
}
