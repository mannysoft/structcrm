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
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="project_id" class="block text-sm font-medium leading-6 text-gray-900">Project</label>
                    <div class="mt-2">
                        <select id="project_id" wire:model="project_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">Select a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('project_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="category" class="block text-sm font-medium leading-6 text-gray-900">Category</label>
                    <div class="mt-2">
                        <select id="category" wire:model="category" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}">{{ ucfirst($cat->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('category') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="caption" class="block text-sm font-medium leading-6 text-gray-900">Caption</label>
                    <div class="mt-2">
                        <input type="text" wire:model="caption" id="caption" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('caption') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="photo" class="block text-sm font-medium leading-6 text-gray-900">Photo</label>
                    <div class="mt-2">
                        <input type="file" wire:model="photo" id="photo" accept="image/*" class="block w-full text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="object-cover w-48 h-48 mt-4 rounded-md">
                    @endif
                    @error('photo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('project_photos.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Upload</button>
        </div>
    </form>
</div>
