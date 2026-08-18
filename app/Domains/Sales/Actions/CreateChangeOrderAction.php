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
            return ChangeOrder::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'description' => $data->description,
                'amount' => $data->amountInCents,
                'status' => $data->status,
            ]);
        });
    }
}
