<?php

declare(strict_types=1);

namespace App\Domains\Sales\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChangeOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'co_number' => $this->co_number,
            'project_id' => $this->project_id,
            'description' => $this->description,
            'amount' => $this->amount / 100,
            'currency' => $this->amount_currency,
            'status' => $this->status->value,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'category' => $item->category->value,
                'description' => $item->description,
                'amount' => $item->amount / 100,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
