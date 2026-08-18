<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'equipment_id' => $this->equipment_id,
            'service_date' => $this->service_date?->toDateString(),
            'description' => $this->description,
            'cost' => $this->cost->toDecimal(),
            'currency' => $this->cost_currency,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
