<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domains\ProjectManagement\Actions\DeleteProjectAction;
use App\Domains\ProjectManagement\Models\Project;

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
            'projects' => Project::query()
                ->with('client')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('project_number', 'like', '%'.$this->search.'%')
                    ->orWhere('title', 'like', '%'.$this->search.'%')))
                ->latest()
                ->paginate(15),
        ];
    }

    public function delete(DeleteProjectAction $action, string $uuid): void
    {
        $project = Project::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($project, 404);

        $this->authorize('delete', $project);

        $action->execute($project);

        session()->flash('status', 'Project deleted.');
    }
}; ?>

<div>
    <x-page-header title="Projects" description="A list of all construction projects in your company.">
        <x-slot name="actions">
            <x-button :href="route('projects.create')">Add project</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search projects…" />
        </div>
    </div>
    
    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Project #</x-table.th>
                                <x-table.th>Title</x-table.th>
                                <x-table.th>Client</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Budget</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($projects as $project)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $project->project_number }}</x-table.td>
                                    <x-table.td class="font-medium text-gray-900">{{ $project->title }}</x-table.td>
                                    <x-table.td>{{ $project->client?->name ?? 'N/A' }}</x-table.td>
                                    <x-table.td>
                                        <x-badge tone="green">{{ ucfirst($project->status->value) }}</x-badge>
                                    </x-table.td>
                                    <x-table.td>${{ number_format($project->budget->toDecimal(), 2) }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('projects.edit', $project) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$project->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No projects found." description="Create one to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('projects.create')">Add project</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $projects->links() }}
    </div>
</div>
