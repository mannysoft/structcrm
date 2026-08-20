<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Budget $budget): bool
    {
        return $user->company_id === $budget->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_budgets');
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->company_id === $budget->company_id && $user->hasPermissionTo('manage_budgets');
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $user->company_id === $budget->company_id && $user->hasPermissionTo('manage_budgets');
    }
}
