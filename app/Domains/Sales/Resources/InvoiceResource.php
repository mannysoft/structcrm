<?php

declare(strict_types=1);

namespace App\Domains\Sales\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'invoice_number' => $this->invoice_number,
            'project_id' => $this->project_id,
            'status' => $this->status->value,
            'total' => $this->total / 100,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
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
