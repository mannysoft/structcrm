<?php

declare(strict_types=1);

namespace App\Domains\Sales\Observers;

use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;

class ChangeOrderObserver
{
    public function creating(ChangeOrder $changeOrder): void
    {
        if (empty($changeOrder->co_number)) {
            $changeOrder->co_number = $this->nextNumber($changeOrder->company_id);
        }

        if ($changeOrder->status === ChangeOrderStatus::Approved && $changeOrder->approved_at === null) {
            $changeOrder->approved_at = now();
        }
    }

    public function updating(ChangeOrder $changeOrder): void
    {
        if ($changeOrder->status === ChangeOrderStatus::Approved && $changeOrder->approved_at === null) {
            $changeOrder->approved_at = now();
        }
    }

    private function nextNumber(?int $companyId): string
    {
        $count = ChangeOrder::withTrashed()
            ->where('company_id', $companyId)
            ->count();

        return sprintf('CO-%d', $count + 1);
    }
}
