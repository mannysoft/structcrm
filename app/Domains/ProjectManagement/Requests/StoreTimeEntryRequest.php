<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\TimeEntryData;
use App\Domains\ProjectManagement\Models\TimeEntry;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TimeEntry::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'clock_in_at' => ['required', 'date'],
            'clock_out_at' => ['nullable', 'date', 'after_or_equal:clock_in_at'],
            'break_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
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
