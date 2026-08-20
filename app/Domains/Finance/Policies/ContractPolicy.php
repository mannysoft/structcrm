<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->company_id === $contract->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_contracts');
    }

    public function update(User $user, Contract $contract): bool
    {
        return $user->company_id === $contract->company_id && $user->hasPermissionTo('manage_contracts');
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->company_id === $contract->company_id && $user->hasPermissionTo('manage_contracts');
    }
}
