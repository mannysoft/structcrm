<?php

declare(strict_types=1);

namespace App\Domains\Sales\Policies;

use App\Domains\Sales\Models\Estimate;
use App\Models\User;

class EstimatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Estimate $estimate): bool
    {
        return $user->company_id === $estimate->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Estimate $estimate): bool
    {
        return $user->company_id === $estimate->company_id;
    }

    public function delete(User $user, Estimate $estimate): bool
    {
        return $user->company_id === $estimate->company_id;
    }
}
