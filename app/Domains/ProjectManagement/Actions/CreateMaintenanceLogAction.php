<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\MaintenanceLogData;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Illuminate\Support\Facades\DB;

final class CreateMaintenanceLogAction
{
    public function execute(MaintenanceLogData $data, int $companyId): MaintenanceLog
    {
        return DB::transaction(function () use ($data, $companyId) {
            $log = new MaintenanceLog([
                'company_id' => $companyId,
                'equipment_id' => $data->equipmentId,
                'service_date' => $data->serviceDate,
                'description' => $data->description,
            ]);

            $log->cost = $data->cost;
            $log->save();

            return $log;
        });
    }
}
