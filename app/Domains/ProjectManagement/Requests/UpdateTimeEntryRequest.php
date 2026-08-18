<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\TimeEntryData;
use Carbon\CarbonImmutable;

class UpdateTimeEntryRequest extends StoreTimeEntryRequest
{
    public function authorize(): bool
    {
        $entry = $this->route('timeEntry');

        return $this->user()?->can('update', $entry) ?? false;
    }

    public function toDTO(): TimeEntryData
    {
        return new TimeEntryData(
            projectId: (int) $this->validated('project_id'),
            clockInAt: CarbonImmutable::parse($this->validated('clock_in_at')),
            clockOutAt: $this->validated('clock_out_at') !== null
                ? CarbonImmutable::parse($this->validated('clock_out_at'))
                : null,
            breakMinutes: (int) ($this->validated('break_minutes') ?? 0),
            notes: $this->validated('notes'),
            latitude: $this->validated('latitude') !== null ? (float) $this->validated('latitude') : null,
            longitude: $this->validated('longitude') !== null ? (float) $this->validated('longitude') : null,
        );
    }
}
