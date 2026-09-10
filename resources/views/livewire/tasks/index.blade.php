<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\ProjectManagement\Actions\DeleteTaskAction;
use App\Domains\ProjectManagement\Models\Task;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'tasks' => Task::query()
                ->with(['project', 'assignee'])
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('project', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))))
                ->latest()
                ->paginate(15),
        ];
    }

    public function delete(DeleteTaskAction $action, string $uuid): void
    {
        $task = Task::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($task, 404);

        $this->authorize('delete', $task);

        $action->execute($task);

        session()->flash('status', 'Task deleted.');
    }
}; ?>

<div>
    <x-page-header title="Tasks" description="A list of all tasks across your projects.">
        <x-slot name="actions">
            <x-button :href="route('tasks.create')">Add task</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search tasks…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Title</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Priority</x-table.th>
                                <x-table.th>Assignee</x-table.th>
                                <x-table.th>Due</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($tasks as $task)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $task->title }}</x-table.td>
                                    <x-table.td>{{ $task->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>
                                        <x-badge tone="sky">{{ ucfirst(str_replace('_', ' ', $task->status->value)) }}</x-badge>
                                    </x-table.td>
                                    <x-table.td>{{ ucfirst($task->priority->value) }}</x-table.td>
                                    <x-table.td>{{ $task->assignee?->name ?? 'Unassigned' }}</x-table.td>
                                    <x-table.td>{{ $task->due_date?->format('M j, Y') ?? 'N/A' }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('tasks.edit', $task) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$task->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <x-empty-state title="No tasks found." description="Add a task to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('tasks.create')">Add task</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $tasks->links() }}
    </div>
</div>
