<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\DailyLogData;
use App\Domains\ProjectManagement\Models\DailyLog;
use Illuminate\Support\Facades\DB;

final class CreateDailyLogAction
{
    public function execute(DailyLogData $data, int $companyId, int $userId): DailyLog
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            return DailyLog::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'user_id' => $userId,
                'log_date' => $data->logDate,
                'weather' => $data->weather,
                'crew_count' => $data->crewCount,
                'labor_hours' => $data->laborHours,
                'equipment_usage' => $data->equipmentUsage,
                'work_performed' => $data->workPerformed,
                'issues' => $data->issues,
                'safety_incidents' => $data->safetyIncidents,
            ]);
        });
    }
}
