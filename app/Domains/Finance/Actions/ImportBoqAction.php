<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Support\Facades\DB;

final class ImportBoqAction
{
    public function execute(array $rows, int $projectId, int $companyId): int
    {
        return DB::transaction(function () use ($rows, $projectId, $companyId) {
            $currency = Company::query()->find($companyId)?->currency?->value ?? 'PHP';
            $imported = 0;

            foreach ($rows as $row) {
                $description = trim((string) ($row['description'] ?? ''));

                if ($description === '') {
                    continue;
                }

                $quantity = (float) ($row['quantity'] ?? 0);
                $unitPrice = Money::fromDecimal((float) ($row['unit_price'] ?? 0), $currency);

                BoqItem::create([
                    'company_id' => $companyId,
                    'project_id' => $projectId,
                    'cost_code_id' => $this->resolveCostCodeId($companyId, $row['cost_code'] ?? null),
                    'item_code' => isset($row['item_code']) && $row['item_code'] !== '' ? (string) $row['item_code'] : null,
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit' => (string) ($row['unit'] ?? 'lot'),
                    'unit_price' => $unitPrice->amountInCents,
                    'total' => (int) round($quantity * $unitPrice->amountInCents),
                    'currency' => $currency,
                ]);

                $imported++;
            }

            return $imported;
        });
    }

    private function resolveCostCodeId(int $companyId, mixed $code): ?int
    {
        if ($code === null || $code === '') {
            return null;
        }

        return CostCode::where('company_id', $companyId)
            ->where('code', (string) $code)
            ->value('id');
    }
}
