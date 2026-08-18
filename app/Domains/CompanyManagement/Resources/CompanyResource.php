<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'tax_number' => $this->tax_number,
            'timezone' => $this->timezone,
            'currency' => $this->currency?->value,
            'logo_url' => $this->logo_path ? Storage::url($this->logo_path) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
