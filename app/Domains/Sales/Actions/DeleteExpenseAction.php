<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\Expense;
use Illuminate\Support\Facades\DB;

final class DeleteExpenseAction
{
    public function execute(Expense $expense): void
    {
        DB::transaction(function () use ($expense) {
            $expense->delete();
        });
    }
}
