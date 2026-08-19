<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Actions;

use App\Models\User;

final class UpdateStaffUserRoleAction
{
    /**
     * Replace the user's role.
     */
    public function execute(User $user, string $role): User
    {
        $user->syncRoles([$role]);

        return $user;
    }
}
