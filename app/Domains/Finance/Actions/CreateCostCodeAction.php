<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\DTOs\CostCodeData;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Support\Facades\DB;

final class CreateCostCodeAction
{
    public function execute(CostCodeData $data, int $companyId): CostCode
    {
        return DB::transaction(function () use ($data, $companyId) {
            return CostCode::create([
                'company_id' => $companyId,
                'code' => $data->code,
                'name' => $data->name,
                'type' => $data->type,
                'parent_id' => $data->parentId,
            ]);
        });
    }
}
