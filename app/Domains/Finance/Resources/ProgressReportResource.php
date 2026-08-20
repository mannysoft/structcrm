<?php

declare(strict_types=1);

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'report_number' => $this->report_number,
            'project_id' => $this->project_id,
            'report_date' => $this->report_date?->format('Y-m-d'),
            'status' => $this->status->value,
            'notes' => $this->notes,
            'total_accomplished' => $this->totalAccomplished() / 100,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'percent_complete' => $item->percent_complete,
                'accomplished_amount' => $item->accomplished_amount / 100,
                'boq_item_id' => $item->boq_item_id,
                'cost_code_id' => $item->cost_code_id,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
