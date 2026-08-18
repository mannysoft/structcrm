<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\PurchaseOrderData;
use App\Domains\Sales\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

final class UpdatePurchaseOrderAction
{
    public function execute(PurchaseOrder $purchaseOrder, PurchaseOrderData $data): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $data) {
            $total = collect($data->items)->sum(
                fn (array $item) => $item['quantity'] * $item['unit_price'],
            );

            $purchaseOrder->update([
                'project_id' => $data->projectId,
                'supplier_id' => $data->supplierId,
                'status' => $data->status,
                'total' => $total,
            ]);

            $purchaseOrder->items()->delete();

            foreach ($data->items as $item) {
                $purchaseOrder->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $purchaseOrder->load('items');
        });
    }
}
