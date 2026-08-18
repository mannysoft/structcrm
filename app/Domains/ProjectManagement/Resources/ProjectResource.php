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
            'project_number' => $this->project_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'budget' => $this->budget->toDecimal(),
            'currency' => $this->budget->currency,
            'client_id' => $this->client_id,
            'manager_id' => $this->manager_id,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
