<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'project_id' => $this->project_id,
            'assigned_user_id' => $this->assigned_user_id,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
