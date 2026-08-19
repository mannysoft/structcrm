<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')->where('company_id', $companyId)],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'budget' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'project_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function toDTO(): ProjectData
    {
        return new ProjectData(
            title: $this->validated('title'),
            clientId: (int) $this->validated('client_id'),
            status: ProjectStatus::from($this->validated('status')),
            budget: Money::fromDecimal((float) $this->validated('budget')),
            description: $this->validated('description'),
            managerId: $this->validated('manager_id') ? (int) $this->validated('manager_id') : null,
            projectNumber: $this->validated('project_number'),
            startDate: $this->validated('start_date') ? CarbonImmutable::parse($this->validated('start_date')) : null,
            endDate: $this->validated('end_date') ? CarbonImmutable::parse($this->validated('end_date')) : null,
        );
    }
}
