<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\Billing;
use App\Models\User;

class BillingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Billing $billing): bool
    {
        return $user->company_id === $billing->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_billings');
    }

    public function update(User $user, Billing $billing): bool
    {
        return $user->company_id === $billing->company_id && $user->hasPermissionTo('manage_billings');
    }

    public function delete(User $user, Billing $billing): bool
    {
        return $user->company_id === $billing->company_id && $user->hasPermissionTo('manage_billings');
    }
}
