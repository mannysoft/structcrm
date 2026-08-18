<?php

declare(strict_types=1);

namespace App\Domains\Sales\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'estimate_number' => $this->estimate_number,
            'title' => $this->title,
            'status' => $this->status->value,
            'subtotal' => $this->subtotal / 100,
            'tax_amount' => $this->tax_amount / 100,
            'total' => $this->amount / 100,
            'currency' => $this->currency,
            'lead_id' => $this->lead_id,
            'project_id' => $this->project_id,
            'valid_until' => $this->valid_until?->format('Y-m-d'),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price / 100,
                    'total' => $item->total / 100,
                ]);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
