<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Policies;

use App\Domains\ProjectManagement\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return $user->company_id === $equipment->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $user->company_id === $equipment->company_id;
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->company_id === $equipment->company_id;
    }
}
