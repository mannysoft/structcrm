<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\EquipmentData;
use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use App\Domains\ProjectManagement\Models\Equipment;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Equipment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(EquipmentType::class)],
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
            'service_due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): EquipmentData
    {
        return new EquipmentData(
            name: $this->validated('name'),
            type: EquipmentType::from($this->validated('type')),
            status: EquipmentStatus::from($this->validated('status')),
            serviceDueAt: $this->validated('service_due_at') !== null
                ? CarbonImmutable::parse($this->validated('service_due_at'))
                : null,
            notes: $this->validated('notes'),
        );
    }
}
