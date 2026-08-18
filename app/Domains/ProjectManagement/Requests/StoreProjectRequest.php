<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Common\ValueObjects\Money;
use Carbon\CarbonImmutable;
use App\Domains\ProjectManagement\Models\Project;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'budget' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function toDTO(): ProjectData
    {
        return new ProjectData(
            name: $this->validated('name'),
            status: ProjectStatus::from($this->validated('status')),
            budget: Money::fromDecimal((float) $this->validated('budget')),
            startDate: $this->validated('start_date') ? CarbonImmutable::parse($this->validated('start_date')) : null,
            endDate: $this->validated('end_date') ? CarbonImmutable::parse($this->validated('end_date')) : null,
        );
    }
}
