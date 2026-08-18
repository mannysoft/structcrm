<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\ExpenseData;
use App\Domains\Sales\Models\Expense;
use Illuminate\Support\Facades\DB;

final class CreateExpenseAction
{
    public function execute(ExpenseData $data, int $companyId): Expense
    {
        return DB::transaction(function () use ($data, $companyId) {
            return Expense::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'category' => $data->category,
                'amount' => $data->amount->amountInCents,
                'currency' => $data->amount->currency,
                'date' => $data->date,
                'receipt_path' => $data->receiptPath,
                'notes' => $data->notes,
            ]);
        });
    }
}
