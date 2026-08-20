<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\BoqItem;
use App\Models\User;

class BoqItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, BoqItem $boqItem): bool
    {
        return $user->company_id === $boqItem->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_boq');
    }

    public function update(User $user, BoqItem $boqItem): bool
    {
        return $user->company_id === $boqItem->company_id && $user->hasPermissionTo('manage_boq');
    }

    public function delete(User $user, BoqItem $boqItem): bool
    {
        return $user->company_id === $boqItem->company_id && $user->hasPermissionTo('manage_boq');
    }
}
