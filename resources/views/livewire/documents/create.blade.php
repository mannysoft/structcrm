<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
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
    <x-page-header title="Upload Document" description="Add a blueprint, permit, contract or spec." />

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
                    <x-input-label for="name" value="Name" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="name" id="name" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" />
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
                    <x-input-label for="file" value="File" />
                    <div class="mt-2">
                        <input type="file" wire:model="file" id="file" class="block w-full text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                    <x-input-error :messages="$errors->get('file')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('documents.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Upload</x-button>
        </div>
    </form>
</div>
