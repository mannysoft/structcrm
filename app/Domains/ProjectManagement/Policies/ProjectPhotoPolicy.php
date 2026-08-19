<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Policies;

use App\Domains\ProjectManagement\Models\ProjectPhoto;
use App\Models\User;

class ProjectPhotoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, ProjectPhoto $photo): bool
    {
        return $user->company_id === $photo->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_photos');
    }

    public function update(User $user, ProjectPhoto $photo): bool
    {
        return $user->company_id === $photo->company_id && $user->hasPermissionTo('manage_photos');
    }

    public function delete(User $user, ProjectPhoto $photo): bool
    {
        return $user->company_id === $photo->company_id && $user->hasPermissionTo('manage_photos');
    }
}
