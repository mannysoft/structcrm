<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Policies;

use App\Domains\ProjectManagement\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, TimeEntry $entry): bool
    {
        return $user->company_id === $entry->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasCapability('manage_time_entries');
    }

    public function update(User $user, TimeEntry $entry): bool
    {
        return $user->company_id === $entry->company_id && $user->hasCapability('manage_time_entries');
    }

    public function delete(User $user, TimeEntry $entry): bool
    {
        return $user->company_id === $entry->company_id && $user->hasCapability('manage_time_entries');
    }
}
