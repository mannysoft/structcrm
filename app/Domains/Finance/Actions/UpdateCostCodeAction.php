<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\DTOs\CostCodeData;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Support\Facades\DB;

final class UpdateCostCodeAction
{
    public function execute(CostCode $costCode, CostCodeData $data): CostCode
    {
        return DB::transaction(function () use ($costCode, $data) {
            $costCode->update([
                'code' => $data->code,
                'name' => $data->name,
                'type' => $data->type,
                'parent_id' => $data->parentId,
            ]);

            return $costCode;
        });
    }
}
