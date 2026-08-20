<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\Budget;
use Illuminate\Support\Facades\DB;

final class DeleteBudgetAction
{
    public function execute(Budget $budget): void
    {
        DB::transaction(function () use ($budget) {
            $budget->items()->delete();
            $budget->delete();
        });
    }
}
