<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Domains\ProjectManagement\Actions\CreateDocumentAction;
use App\Domains\ProjectManagement\DTOs\DocumentData;
use App\Domains\ProjectManagement\Enums\DocumentCategory;
use App\Domains\ProjectManagement\Models\Project;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|in:blueprint,cad,permit,contract,spec')]
    public string $category = 'contract';

    #[Validate('required|file|max:10240')]
    public $file;

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
            'categories' => DocumentCategory::cases(),
        ];
    }

    public function save(CreateDocumentAction $action): void
    {
        $this->validate();

        $filePath = $this->file->store('documents');

        $action->execute(new DocumentData(
            projectId: (int) $this->project_id,
            name: $this->name,
            category: DocumentCategory::from($this->category),
            filePath: $filePath,
        ), auth()->user()->company_id);

        session()->flash('status', 'Document uploaded.');

        $this->redirect(route('documents.index'), navigate: true);
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
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Name</label>
                    <div class="mt-2">
                        <input type="text" wire:model="name" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
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
                    <label for="file" class="block text-sm font-medium leading-6 text-gray-900">File</label>
                    <div class="mt-2">
                        <input type="file" wire:model="file" id="file" class="block w-full text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    @error('file') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('documents.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Upload</button>
        </div>
    </form>
</div>
