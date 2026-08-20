<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Models\Budget;
use Illuminate\Support\Facades\DB;

final class CreateBudgetAction
{
    public function execute(BudgetData $data, int $companyId): Budget
    {
        return DB::transaction(function () use ($data, $companyId) {
            $latestVersion = (int) Budget::where('project_id', $data->projectId)->max('version');
            $currency = $data->forecast?->currency ?? (Company::find($companyId)?->currency?->value ?? 'PHP');

            Budget::where('project_id', $data->projectId)->update(['is_current' => false]);

            $budget = Budget::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'type' => $data->type,
                'version' => $latestVersion + 1,
                'is_current' => true,
                'status' => $data->status,
                'currency' => $currency,
                'forecast_amount' => $data->forecast?->amountInCents,
            ]);

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
