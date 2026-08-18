<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

final class DeletePurchaseOrderAction
{
    public function execute(PurchaseOrder $purchaseOrder): void
    {
        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->delete();
        });
    }
}
