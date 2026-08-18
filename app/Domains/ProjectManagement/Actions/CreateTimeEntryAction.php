<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\TimeEntryData;
use App\Domains\ProjectManagement\Models\TimeEntry;
use Illuminate\Support\Facades\DB;

final class CreateTimeEntryAction
{
    public function execute(TimeEntryData $data, int $companyId, int $userId): TimeEntry
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            return TimeEntry::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'user_id' => $userId,
                'clock_in_at' => $data->clockInAt,
                'clock_out_at' => $data->clockOutAt,
                'break_minutes' => $data->breakMinutes,
                'notes' => $data->notes,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
            ]);
        });
    }
}
