<?php

declare(strict_types=1);

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CostCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type->value,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
