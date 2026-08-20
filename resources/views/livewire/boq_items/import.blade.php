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
    <x-page-header title="Import BOQ" description="Upload an Excel or CSV file to import BOQ line items for a project." />

    <form wire:submit="import" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

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
                    <x-input-label for="file" value="File (.xlsx, .xls, .csv)" />
                    <div class="mt-2">
                        <input type="file" wire:model="file" id="file" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                    <x-input-error :messages="$errors->get('file')" />
                </div>
            </div>

            <div class="mt-6 text-sm text-gray-500">
                <p>Expected columns: <code>item_code</code>, <code>description</code>, <code>unit</code>, <code>quantity</code>, <code>unit_price</code>, <code>cost_code</code>.</p>
                <a href="{{ route('boq_items.template') }}" class="mt-1 inline-block font-semibold text-brand-600 hover:text-brand-900">Download template</a>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('boq_items.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Import</x-button>
        </div>
    </form>
</div>
