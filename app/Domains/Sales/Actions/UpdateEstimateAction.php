<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\EstimateData;
use App\Domains\Sales\Models\Estimate;
use Illuminate\Support\Facades\DB;

final class UpdateEstimateAction
{
    public function execute(Estimate $estimate, EstimateData $data): Estimate
    {
        return DB::transaction(function () use ($estimate, $data) {
            $subtotal = collect($data->items)->sum(
                fn (array $item) => $item['quantity'] * $item['unit_price'],
            );
            $taxAmount = (int) round($subtotal * $data->taxRate / 100);
            $total = $subtotal + $taxAmount;

            $estimate->update([
                'lead_id' => $data->leadId,
                'project_id' => $data->projectId,
                'title' => $data->title,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'amount' => $total,
                'status' => $data->status,
                'valid_until' => $data->validUntil,
            ]);

            $estimate->items()->delete();

            foreach ($data->items as $item) {
                $estimate->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $estimate->load('items');
        });
    }
}
