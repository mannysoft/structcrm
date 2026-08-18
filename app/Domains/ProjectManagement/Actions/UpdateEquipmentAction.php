<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\EquipmentData;
use App\Domains\ProjectManagement\Models\Equipment;
use Illuminate\Support\Facades\DB;

final class UpdateEquipmentAction
{
    public function execute(Equipment $equipment, EquipmentData $data): Equipment
    {
        return DB::transaction(function () use ($equipment, $data) {
            $equipment->update([
                'name' => $data->name,
                'type' => $data->type,
                'status' => $data->status,
                'service_due_at' => $data->serviceDueAt,
                'notes' => $data->notes,
            ]);

            return $equipment;
        });
    }
}
