<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\CostCode;
use App\Models\User;

class CostCodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, CostCode $costCode): bool
    {
        return $user->company_id === $costCode->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_cost_codes');
    }

    public function update(User $user, CostCode $costCode): bool
    {
        return $user->company_id === $costCode->company_id && $user->hasPermissionTo('manage_cost_codes');
    }

    public function delete(User $user, CostCode $costCode): bool
    {
        return $user->company_id === $costCode->company_id && $user->hasPermissionTo('manage_cost_codes');
    }
}
