<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Models\Budget;
use Illuminate\Support\Facades\DB;

final class UpdateBudgetAction
{
    public function execute(Budget $budget, BudgetData $data): Budget
    {
        return DB::transaction(function () use ($budget, $data) {
            $currency = $data->forecast?->currency ?? $budget->currency;

            $budget->update([
                'project_id' => $data->projectId,
                'type' => $data->type,
                'status' => $data->status,
                'currency' => $currency,
                'forecast_amount' => $data->forecast?->amountInCents,
            ]);

            $budget->items()->delete();

            foreach ($data->items as $item) {
                $budget->items()->create([
                    'cost_code_id' => $item['cost_code_id'] ?? null,
                    'boq_item_id' => $item['boq_item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'lot',
                    'unit_price' => $item['unit_price'],
                    'total' => (int) round($item['quantity'] * $item['unit_price']),
                ]);
            }

            return $budget->load('items');
        });
    }
}
