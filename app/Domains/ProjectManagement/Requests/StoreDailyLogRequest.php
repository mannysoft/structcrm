<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\DailyLogData;
use App\Domains\ProjectManagement\Models\DailyLog;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DailyLog::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'log_date' => ['required', 'date'],
            'work_performed' => ['required', 'string'],
            'weather' => ['nullable', 'string', 'max:100'],
            'crew_count' => ['nullable', 'integer', 'min:0'],
            'labor_hours' => ['nullable', 'numeric', 'min:0'],
            'equipment_usage' => ['nullable', 'string'],
            'issues' => ['nullable', 'string'],
            'safety_incidents' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): DailyLogData
    {
        return new DailyLogData(
            projectId: (int) $this->validated('project_id'),
            logDate: CarbonImmutable::parse($this->validated('log_date')),
            workPerformed: $this->validated('work_performed'),
            weather: $this->validated('weather'),
            crewCount: (int) ($this->validated('crew_count') ?? 0),
            laborHours: (float) ($this->validated('labor_hours') ?? 0),
            equipmentUsage: $this->validated('equipment_usage'),
            issues: $this->validated('issues'),
            safetyIncidents: $this->validated('safety_incidents'),
        );
    }
}
