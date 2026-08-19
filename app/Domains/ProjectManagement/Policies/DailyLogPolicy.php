<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Policies;

use App\Domains\ProjectManagement\Models\DailyLog;
use App\Models\User;

class DailyLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, DailyLog $dailyLog): bool
    {
        return $user->company_id === $dailyLog->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_daily_logs');
    }

    public function update(User $user, DailyLog $dailyLog): bool
    {
        return $user->company_id === $dailyLog->company_id && $user->hasPermissionTo('manage_daily_logs');
    }

    public function delete(User $user, DailyLog $dailyLog): bool
    {
        return $user->company_id === $dailyLog->company_id && $user->hasPermissionTo('manage_daily_logs');
    }
}
