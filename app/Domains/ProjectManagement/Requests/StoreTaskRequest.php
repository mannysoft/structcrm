<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\TaskData;
use App\Domains\ProjectManagement\Enums\TaskPriority;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function toDTO(): TaskData
    {
        return new TaskData(
            title: $this->validated('title'),
            projectId: (int) $this->validated('project_id'),
            status: TaskStatus::from($this->validated('status')),
            priority: TaskPriority::from($this->validated('priority')),
            description: $this->validated('description'),
            assignedUserId: $this->validated('assigned_user_id') ? (int) $this->validated('assigned_user_id') : null,
            dueDate: $this->validated('due_date') ? CarbonImmutable::parse($this->validated('due_date')) : null,
        );
    }
}
