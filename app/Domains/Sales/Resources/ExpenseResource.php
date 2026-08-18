<?php

declare(strict_types=1);

namespace App\Domains\Sales\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'project_id' => $this->project_id,
            'category' => $this->category->value,
            'amount' => $this->expenseAmount->toDecimal(),
            'currency' => $this->expenseAmount->currency,
            'date' => $this->date?->format('Y-m-d'),
            'receipt_url' => $this->receipt_path ? Storage::url($this->receipt_path) : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
