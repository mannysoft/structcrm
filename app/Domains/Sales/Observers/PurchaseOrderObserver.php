<?php

declare(strict_types=1);

namespace App\Domains\Sales\Observers;

use App\Domains\Sales\Models\PurchaseOrder;

class PurchaseOrderObserver
{
    public function creating(PurchaseOrder $purchaseOrder): void
    {
        if (empty($purchaseOrder->po_number)) {
            $purchaseOrder->po_number = $this->nextNumber($purchaseOrder->company_id);
        }
    }

    private function nextNumber(?int $companyId): string
    {
        $count = PurchaseOrder::withTrashed()
            ->where('company_id', $companyId)
            ->count();

        return sprintf('PO-%d', $count + 1);
    }
}
