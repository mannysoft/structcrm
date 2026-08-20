<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\DTOs\BoqItemData;
use App\Domains\Finance\Models\BoqItem;
use Illuminate\Support\Facades\DB;

final class UpdateBoqItemAction
{
    public function execute(BoqItem $boqItem, BoqItemData $data): BoqItem
    {
        return DB::transaction(function () use ($boqItem, $data) {
            $boqItem->update([
                'project_id' => $data->projectId,
                'cost_code_id' => $data->costCodeId,
                'item_code' => $data->itemCode,
                'description' => $data->description,
                'quantity' => $data->quantity,
                'unit' => $data->unit,
                'unit_price' => $data->unitPrice->amountInCents,
                'total' => (int) round($data->quantity * $data->unitPrice->amountInCents),
                'currency' => $data->unitPrice->currency,
            ]);

            return $boqItem;
        });
    }
}
