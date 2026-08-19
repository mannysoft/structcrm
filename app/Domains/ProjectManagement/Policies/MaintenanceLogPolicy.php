<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Policies;

use App\Domains\ProjectManagement\Models\MaintenanceLog;
use App\Models\User;

class MaintenanceLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, MaintenanceLog $log): bool
    {
        return $user->company_id === $log->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_maintenance_logs');
    }

    public function update(User $user, MaintenanceLog $log): bool
    {
        return $user->company_id === $log->company_id && $user->hasPermissionTo('manage_maintenance_logs');
    }

    public function delete(User $user, MaintenanceLog $log): bool
    {
        return $user->company_id === $log->company_id && $user->hasPermissionTo('manage_maintenance_logs');
    }
}
