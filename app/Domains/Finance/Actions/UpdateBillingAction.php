<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\DTOs\BillingData;
use App\Domains\Finance\Models\Billing;
use Illuminate\Support\Facades\DB;

final class UpdateBillingAction
{
    public function execute(Billing $billing, BillingData $data): Billing
    {
        return DB::transaction(function () use ($billing, $data) {
            $gross = collect($data->items)->sum(fn (array $item) => (int) round($item['quantity'] * $item['unit_price']));

            $retentionRate = $data->retentionRate ?? 0;
            $retention = (int) round($gross * $retentionRate);

            $previous = (int) Billing::where('project_id', $data->projectId)
                ->where('id', '!=', $billing->id)
                ->sum('net_amount');

            $net = $gross - $retention - $data->otherDeductions;

            $billing->update([
                'project_id' => $data->projectId,
                'progress_report_id' => $data->progressReportId,
                'period_start' => $data->periodStart,
                'period_end' => $data->periodEnd,
                'previous_billing_amount' => $previous,
                'current_accomplishment_amount' => $gross,
                'gross_amount' => $gross,
                'retention_rate' => $retentionRate,
                'retention_amount' => $retention,
                'other_deductions_amount' => $data->otherDeductions,
                'net_amount' => $net,
                'currency' => $data->currency,
                'status' => $data->status,
                'certified_at' => $data->status->certifies() ? ($billing->certified_at ?? now()) : null,
                'due_date' => $data->dueDate,
                'notes' => $data->notes,
            ]);

            $billing->items()->delete();

            foreach ($data->items as $item) {
                $billing->items()->create([
                    'boq_item_id' => $item['boq_item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => (int) round($item['quantity'] * $item['unit_price']),
                ]);
            }

            return $billing->load('items');
        });
    }
}
