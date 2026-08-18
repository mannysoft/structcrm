<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\TimeEntryData;
use App\Domains\ProjectManagement\Models\TimeEntry;
use Illuminate\Support\Facades\DB;

final class UpdateTimeEntryAction
{
    public function execute(TimeEntry $entry, TimeEntryData $data): TimeEntry
    {
        return DB::transaction(function () use ($entry, $data) {
            $entry->update([
                'project_id' => $data->projectId,
                'clock_in_at' => $data->clockInAt,
                'clock_out_at' => $data->clockOutAt,
                'break_minutes' => $data->breakMinutes,
                'notes' => $data->notes,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
            ]);

            return $entry;
        });
    }
}
