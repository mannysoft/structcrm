<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\CostCode;
use Illuminate\Support\Facades\DB;

final class DeleteCostCodeAction
{
    public function execute(CostCode $costCode): void
    {
        DB::transaction(function () use ($costCode) {
            $costCode->delete();
        });
    }
}
