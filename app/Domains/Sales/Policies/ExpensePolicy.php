<?php

declare(strict_types=1);

namespace App\Domains\Sales\Policies;

use App\Domains\Sales\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->company_id === $expense->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->company_id === $expense->company_id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->company_id === $expense->company_id;
    }
}
