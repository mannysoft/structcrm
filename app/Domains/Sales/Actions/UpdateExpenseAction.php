<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\ExpenseData;
use App\Domains\Sales\Models\Expense;
use Illuminate\Support\Facades\DB;

final class UpdateExpenseAction
{
    public function execute(Expense $expense, ExpenseData $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $expense->update([
                'project_id' => $data->projectId,
                'category' => $data->category,
                'amount' => $data->amount->amountInCents,
                'currency' => $data->amount->currency,
                'date' => $data->date,
                'receipt_path' => $data->receiptPath,
                'notes' => $data->notes,
            ]);

            return $expense;
        });
    }
}
