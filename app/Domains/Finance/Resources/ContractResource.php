<?php

declare(strict_types=1);

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'contract_number' => $this->contract_number,
            'title' => $this->title,
            'project_id' => $this->project_id,
            'contract_value' => $this->contractValue->toDecimal(),
            'currency' => $this->contract_value_currency,
            'status' => $this->status->value,
            'signed_at' => $this->signed_at?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
