<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

class UpdateEquipmentRequest extends StoreEquipmentRequest
{
    public function authorize(): bool
    {
        $equipment = $this->route('equipment');

        return $this->user()?->can('update', $equipment) ?? false;
    }
}
