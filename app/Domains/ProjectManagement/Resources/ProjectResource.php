<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status->value,
            'budget' => $this->budget->toDecimal(),
            'currency' => $this->budget->currency,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
