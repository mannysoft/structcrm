<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Domains\ProjectManagement\Actions\CreateProjectPhotoAction;
use App\Domains\ProjectManagement\DTOs\ProjectPhotoData;
use App\Domains\ProjectManagement\Enums\PhotoCategory;
use App\Domains\ProjectManagement\Models\Project;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|in:before,during,after,inspection,safety')]
    public string $category = 'during';

    #[Validate('nullable|string|max:255')]
    public string $caption = '';

    #[Validate('required|image|max:5120')]
    public $photo;

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
            'categories' => PhotoCategory::cases(),
        ];
    }

    public function save(CreateProjectPhotoAction $action): void
    {
        $this->validate();

        $filePath = $this->photo->store('project-photos');

        $action->execute(new ProjectPhotoData(
            projectId: (int) $this->project_id,
            category: PhotoCategory::from($this->category),
            filePath: $filePath,
            caption: $this->caption ?: null,
        ), auth()->user()->company_id, auth()->user()->id);

        session()->flash('status', 'Photo uploaded.');

        $this->redirect(route('project_photos.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Upload Photo" description="Add a progress or inspection photo." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
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
                    <x-input-label for="category" value="Category" />
                    <div class="mt-2">
                        <x-select id="category" wire:model="category">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}">{{ ucfirst($cat->value) }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('category')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="caption" value="Caption" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="caption" id="caption" />
                    </div>
                    <x-input-error :messages="$errors->get('caption')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="photo" value="Photo" />
                    <div class="mt-2">
                        <input type="file" wire:model="photo" id="photo" accept="image/*" class="block w-full text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="mt-4 size-48 rounded-lg object-cover">
                    @endif
                    <x-input-error :messages="$errors->get('photo')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('project_photos.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Upload</x-button>
        </div>
    </form>
</div>
