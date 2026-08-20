<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\Contract;
use Illuminate\Support\Facades\DB;

final class DeleteContractAction
{
    public function execute(Contract $contract): void
    {
        DB::transaction(function () use ($contract) {
            $contract->delete();
        });
    }
}
