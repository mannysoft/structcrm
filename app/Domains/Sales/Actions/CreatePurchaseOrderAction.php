<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\PurchaseOrderData;
use App\Domains\Sales\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

final class CreatePurchaseOrderAction
{
    public function execute(PurchaseOrderData $data, int $companyId): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $companyId) {
            $total = collect($data->items)->sum(
                fn (array $item) => $item['quantity'] * $item['unit_price'],
            );

            $purchaseOrder = PurchaseOrder::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'supplier_id' => $data->supplierId,
                'status' => $data->status,
                'total' => $total,
            ]);

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
