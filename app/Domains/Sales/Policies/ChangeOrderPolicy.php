<?php

declare(strict_types=1);

namespace App\Domains\Sales\Policies;

use App\Domains\Sales\Models\ChangeOrder;
use App\Models\User;

class ChangeOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->company_id === $changeOrder->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->company_id === $changeOrder->company_id;
    }

    public function delete(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->company_id === $changeOrder->company_id;
    }
}
