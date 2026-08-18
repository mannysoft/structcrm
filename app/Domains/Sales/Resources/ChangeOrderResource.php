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
            'status' => $this->status->value,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
