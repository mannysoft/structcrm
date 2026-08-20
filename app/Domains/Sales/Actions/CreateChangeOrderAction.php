<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\ChangeOrderData;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Support\Facades\DB;

final class CreateChangeOrderAction
{
    public function execute(ChangeOrderData $data, int $companyId): ChangeOrder
    {
        return DB::transaction(function () use ($data, $companyId) {
            $changeOrder = ChangeOrder::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'description' => $data->description,
                'amount' => $data->amountInCents,
                'amount_currency' => $data->amountCurrency,
                'status' => $data->status,
            ]);

            foreach ($data->items as $item) {
                $changeOrder->items()->create([
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);
            }

            return $changeOrder->load('items');
        });
    }
}
