<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Domains\ProjectManagement\Actions\CreateTaskAction;
use App\Domains\ProjectManagement\DTOs\TaskData;
use App\Domains\ProjectManagement\Enums\TaskPriority;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    public function mount(): void
    {
        $this->project_id = session('active_project_id') ? (string) session('active_project_id') : '';
    }
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('required|string')]
    public string $status = 'to_do';

    #[Validate('required|string')]
    public string $priority = 'medium';

    #[Validate('nullable|integer|exists:users,id')]
    public string $assigned_user_id = '';

    #[Validate('nullable|date')]
    public string $due_date = '';

    public function with(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'projects' => Project::where('company_id', $companyId)->latest()->get(),
            'users' => User::where('company_id', $companyId)->get(),
        ];
    }

    public function save(CreateTaskAction $action): void
    {
        $this->validate();

        $dto = new TaskData(
            title: $this->title,
            projectId: (int) $this->project_id,
            status: TaskStatus::from($this->status),
            priority: TaskPriority::from($this->priority),
            description: $this->description ?: null,
            assignedUserId: $this->assigned_user_id ? (int) $this->assigned_user_id : null,
            dueDate: $this->due_date ? CarbonImmutable::parse($this->due_date) : null,
        );

        $action->execute($dto, auth()->user()->company_id);

        session()->flash('status', 'Task successfully created.');

        $this->redirect(route('tasks.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Task" description="Create a new task for a project." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-4">
                    <x-input-label for="title" value="Title" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="title" id="title" />
                    </div>
                    <x-input-error :messages="$errors->get('title')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="project_id" value="Project" />
                    <div class="mt-2">
                        <x-select id="project_id" wire:model="project_id">
                            <option value="">Select a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('project_id')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="assigned_user_id" value="Assignee" />
                    <div class="mt-2">
                        <x-select id="assigned_user_id" wire:model="assigned_user_id">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('assigned_user_id')" />
                </div>

                <div class="col-span-full">
                    <x-input-label for="description" value="Description" />
                    <div class="mt-2">
                        <x-textarea id="description" wire:model="description" rows="3"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('description')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="to_do">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="in_review">In Review</option>
                            <option value="done">Done</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="priority" value="Priority" />
                    <div class="mt-2">
                        <x-select id="priority" wire:model="priority">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('priority')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="due_date" value="Due Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="due_date" id="due_date" />
                    </div>
                    <x-input-error :messages="$errors->get('due_date')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('tasks.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
