<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Domains\Finance\Actions\ImportBoqAction;
use App\Domains\Finance\Imports\BoqImport;
use App\Domains\ProjectManagement\Models\Project;
use Maatwebsite\Excel\Facades\Excel;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|file|mimes:xlsx,xls,csv|max:2048')]
    public $file;

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function import(ImportBoqAction $action): void
    {
        $this->validate();

        $rows = Excel::toArray(new BoqImport, $this->file->getRealPath())[0] ?? [];

        $count = $action->execute($rows, (int) $this->project_id, auth()->user()->company_id);

        session()->flash('status', $count.' BOQ items imported.');

        $this->redirect(route('boq_items.index'), navigate: true);
    }
}; ?>

<div>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Import BOQ</h1>
            <p class="mt-2 text-sm text-gray-700">Upload an Excel or CSV file to import BOQ line items for a project.</p>
        </div>
    </div>

    <form wire:submit="import" class="mt-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

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
                    <label for="file" class="block text-sm font-medium leading-6 text-gray-900">File (.xlsx, .xls, .csv)</label>
                    <div class="mt-2">
                        <input type="file" wire:model="file" id="file" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    @error('file') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 text-sm text-gray-500">
                <p>Expected columns: <code>item_code</code>, <code>description</code>, <code>unit</code>, <code>quantity</code>, <code>unit_price</code>, <code>cost_code</code>.</p>
                <a href="{{ route('boq_items.template') }}" class="mt-1 inline-block font-semibold text-indigo-600 hover:text-indigo-900">Download template</a>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('boq_items.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Import</button>
        </div>
    </form>
</div>
