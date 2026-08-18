<?php

declare(strict_types=1);

namespace App\Domains\Sales\Policies;

use App\Domains\Sales\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->company_id === $lead->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->company_id === $lead->company_id;
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->company_id === $lead->company_id;
    }
}
