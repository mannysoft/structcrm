<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\ProjectManagement\Actions\DeleteProjectPhotoAction;
use App\Domains\ProjectManagement\Models\ProjectPhoto;

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
            'photos' => ProjectPhoto::query()
                ->with('project', 'uploader')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('caption', 'like', '%'.$this->search.'%')
                    ->orWhereHas('project', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))))
                ->when(session('active_project_id'), fn ($query) => $query->where('project_id', session('active_project_id')))
                ->latest()
                ->paginate(15),
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
    <x-page-header title="Site Photos" description="Progress and inspection photos captured in the field.">
        <x-slot name="actions">
            <x-button :href="route('project_photos.create')">Upload photo</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search photos…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Category</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Caption</x-table.th>
                                <x-table.th>Uploaded</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($photos as $photo)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $photo->category->value }}</x-table.td>
                                    <x-table.td>{{ $photo->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $photo->caption ?? '—' }}</x-table.td>
                                    <x-table.td>{{ $photo->created_at?->format('M j, Y') }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <x-confirm-delete :uuid="$photo->uuid">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No photos yet." description="Upload your first site photo.">
                                            <x-slot name="action">
                                                <x-button :href="route('project_photos.create')">Upload photo</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $photos->links() }}
    </div>
</div>
