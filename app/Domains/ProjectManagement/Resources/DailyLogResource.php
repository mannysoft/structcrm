<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
            'log_date' => $this->log_date?->format('Y-m-d'),
            'weather' => $this->weather,
            'crew_count' => $this->crew_count,
            'labor_hours' => $this->labor_hours,
            'equipment_usage' => $this->equipment_usage,
            'work_performed' => $this->work_performed,
            'issues' => $this->issues,
            'safety_incidents' => $this->safety_incidents,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
