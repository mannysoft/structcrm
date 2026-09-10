<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Domains\Finance\Actions\CreateProgressReportAction;
use App\Domains\Finance\DTOs\ProgressReportData;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\ProjectManagement\Models\Project;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|date')]
    public string $report_date = '';

    #[Validate('required|string')]
    public string $status = 'draft';

    #[Validate('nullable|string')]
    public string $notes = '';

    public array $items = [];

    public function mount(): void
    {
        $this->report_date = now()->format('Y-m-d');
    }

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(CreateProgressReportAction $action): void
    {
        $this->validate();

        $this->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.percent_complete' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.accomplished_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $items = collect($this->items)->map(fn (array $item) => [
            'description' => $item['description'],
            'quantity' => (float) $item['quantity'],
            'percent_complete' => (float) ($item['percent_complete'] ?? 0),
            'accomplished_amount' => (int) round((float) $item['accomplished_amount'] * 100),
        ])->all();

        $dto = new ProgressReportData(
            projectId: (int) $this->project_id,
            reportDate: CarbonImmutable::parse($this->report_date),
            status: ProgressReportStatus::from($this->status),
            notes: $this->notes ?: null,
            items: $items,
        );

        $action->execute($dto, auth()->user()->company_id);

        session()->flash('status', 'Progress report created.');

        $this->redirect(route('progress_reports.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Report" description="Record a site accomplishment report." />

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
                    <x-input-label for="report_date" value="Report Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="report_date" id="report_date" />
                    </div>
                    <x-input-error :messages="$errors->get('report_date')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div class="col-span-full">
                    <x-input-label for="notes" value="Notes" />
                    <div class="mt-2">
                        <x-textarea id="notes" wire:model="notes" rows="2"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>

                <div class="col-span-full" x-data="{ items: @entangle('items') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Accomplishment Items</h3>
                        <button type="button" @click="items.push({ description: '', quantity: 1, percent_complete: 0, accomplished_amount: '0.00' })" class="text-sm font-semibold text-brand-600 hover:text-brand-500">Add item</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-5">
                                    <x-text-input x-model="item.description" placeholder="Description" />
                                </div>
                                <div class="col-span-2">
                                    <x-text-input type="number" step="0.01" x-model="item.quantity" min="0" placeholder="Qty" />
                                </div>
                                <div class="col-span-2">
                                    <x-text-input type="number" step="0.01" x-model="item.percent_complete" min="0" max="100" placeholder="%" />
                                </div>
                                <div class="col-span-2">
                                    <x-text-input type="number" step="0.01" x-model="item.accomplished_amount" placeholder="Amount" />
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <button type="button" @click="items.splice(index, 1)" class="text-sm text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('items')" />
                    <x-input-error :messages="$errors->get('items.*.description')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('progress_reports.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
