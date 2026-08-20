<?php

declare(strict_types=1);

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'project_id' => $this->project_id,
            'type' => $this->type->value,
            'version' => $this->version,
            'is_current' => $this->is_current,
            'status' => $this->status->value,
            'currency' => $this->currency,
            'total' => $this->totalAmount() / 100,
            'forecast' => $this->forecast()?->toDecimal(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price / 100,
                'total' => $item->total / 100,
                'cost_code_id' => $item->cost_code_id,
                'boq_item_id' => $item->boq_item_id,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
