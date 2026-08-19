<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Actions\DeleteProjectAction;
use App\Domains\ProjectManagement\Models\Project;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'projects' => Project::with('client')->latest()->get(),
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
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Projects</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all construction projects in your company.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('projects.create') }}" wire:navigate class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Add project
            </a>
        </div>
    </div>
    
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Project #</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Title</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Client</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Budget</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($projects as $project)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $project->project_number }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ $project->title }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $project->client?->name ?? 'N/A' }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                            {{ ucfirst($project->status->value) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">${{ number_format($project->budget->toDecimal(), 2) }}</td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="{{ route('projects.edit', $project) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <button wire:click="delete('{{ $project->uuid }}')" wire:confirm="Are you sure you want to delete this project?" class="ml-4 text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6
                                    <td colspan="5" class="px-3 py-8 text-sm text-center text-gray-500">
                                        No projects found. Create one to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
