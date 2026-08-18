<?php

declare(strict_types=1);

namespace App\Domains\Sales\Policies;

use App\Domains\Sales\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->company_id === $purchaseOrder->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->company_id === $purchaseOrder->company_id;
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->company_id === $purchaseOrder->company_id;
    }
}
