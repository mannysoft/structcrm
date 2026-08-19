<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;

final class DecideChangeOrderAction
{
    /**
     * Approve or reject a change order.
     */
    public function execute(ChangeOrder $changeOrder, ChangeOrderStatus $status): ChangeOrder
    {
        $changeOrder->forceFill([
            'status' => $status,
            'approved_at' => $status === ChangeOrderStatus::Approved ? now() : null,
        ])->save();

        return $changeOrder;
    }
}
