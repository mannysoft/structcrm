<?php

declare(strict_types=1);

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'billing_number' => $this->billing_number,
            'project_id' => $this->project_id,
            'progress_report_id' => $this->progress_report_id,
            'period_start' => $this->period_start?->format('Y-m-d'),
            'period_end' => $this->period_end?->format('Y-m-d'),
            'previous_billing_amount' => $this->previous_billing_amount / 100,
            'current_accomplishment_amount' => $this->current_accomplishment_amount / 100,
            'gross_amount' => $this->gross_amount / 100,
            'retention_amount' => $this->retention_amount / 100,
            'other_deductions_amount' => $this->other_deductions_amount / 100,
            'net_amount' => $this->net_amount / 100,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'collection_status' => $this->collectionStatus(),
            'certified_at' => $this->certified_at?->toIso8601String(),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'paid_amount' => $this->paidAmount() / 100,
            'balance_amount' => $this->balanceAmount() / 100,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price / 100,
                'total' => $item->total / 100,
                'boq_item_id' => $item->boq_item_id,
            ])),
            'payments' => $this->whenLoaded('payments', fn () => $this->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'payment_date' => $payment->payment_date?->format('Y-m-d'),
                'amount' => $payment->amount / 100,
                'method' => $payment->method,
                'reference' => $payment->reference,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
