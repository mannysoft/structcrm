<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\EquipmentData;
use App\Domains\ProjectManagement\Models\Equipment;
use Illuminate\Support\Facades\DB;

final class CreateEquipmentAction
{
    public function execute(EquipmentData $data, int $companyId): Equipment
    {
        return DB::transaction(function () use ($data, $companyId) {
            return Equipment::create([
                'company_id' => $companyId,
                'name' => $data->name,
                'type' => $data->type,
                'status' => $data->status,
                'service_due_at' => $data->serviceDueAt,
                'notes' => $data->notes,
            ]);
        });
    }
}
