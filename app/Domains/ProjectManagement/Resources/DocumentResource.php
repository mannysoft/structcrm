<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'category' => $this->category->value,
            'file_url' => Storage::url($this->file_path),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
