<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Actions\DeleteProjectPhotoAction;
use App\Domains\ProjectManagement\Models\ProjectPhoto;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'photos' => ProjectPhoto::with('project', 'uploader')->latest()->get(),
        ];
    }

    public function delete(DeleteProjectPhotoAction $action, string $uuid): void
    {
        $photo = ProjectPhoto::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($photo, 404);

        $this->authorize('delete', $photo);

        $action->execute($photo);

        session()->flash('status', 'Photo deleted.');
    }
}; ?>

<div>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Site Photos</h1>
            <p class="mt-2 text-sm text-gray-700">Progress and inspection photos captured in the field.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('project_photos.create') }}" wire:navigate class="block px-3 py-2 text-sm font-semibold text-center text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Upload photo
            </a>
        </div>
    </div>

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Category</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Project</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Caption</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Uploaded</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($photos as $photo)
                                <tr>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6">{{ $photo->category->value }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $photo->project?->title ?? 'N/A' }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500">{{ $photo->caption ?? '—' }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $photo->created_at?->format('M j, Y') }}</td>
                                    <td class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6">
                                        <button wire:click="delete('{{ $photo->uuid }}')" wire:confirm="Are you sure you want to delete this photo?" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8 text-sm text-center text-gray-500">
                                        No photos yet. Upload your first site photo.
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
