<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\ProgressReport;
use App\Models\User;

class ProgressReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, ProgressReport $progressReport): bool
    {
        return $user->company_id === $progressReport->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_progress_reports');
    }

    public function update(User $user, ProgressReport $progressReport): bool
    {
        return $user->company_id === $progressReport->company_id && $user->hasPermissionTo('manage_progress_reports');
    }

    public function delete(User $user, ProgressReport $progressReport): bool
    {
        return $user->company_id === $progressReport->company_id && $user->hasPermissionTo('manage_progress_reports');
    }
}
