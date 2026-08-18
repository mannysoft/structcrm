<?php

declare(strict_types=1);

namespace App\Domains\Sales\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'po_number' => $this->po_number,
            'project_id' => $this->project_id,
            'supplier_id' => $this->supplier_id,
            'status' => $this->status->value,
            'total' => $this->total / 100,
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
