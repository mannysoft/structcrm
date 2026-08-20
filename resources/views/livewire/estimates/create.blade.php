<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateEstimateAction;
use App\Domains\Sales\DTOs\EstimateData;
use App\Domains\Sales\Enums\EstimateStatus;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string')]
    public string $status = 'draft';

    #[Validate('nullable|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('nullable|date')]
    public string $valid_until = '';

    #[Validate('nullable|numeric|min:0|max:100')]
    public string $tax_rate = '0';

    public array $items = [];

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(CreateEstimateAction $action): void
    {
        $this->validate();

        $this->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $items = collect($this->items)->map(fn (array $item) => [
            'description' => $item['description'],
            'quantity' => (int) $item['quantity'],
            'unit_price' => (int) round((float) $item['unit_price'] * 100),
        ])->all();

        $action->execute(new EstimateData(
            title: $this->title,
            status: EstimateStatus::from($this->status),
            items: $items,
            projectId: $this->project_id ? (int) $this->project_id : null,
            validUntil: $this->valid_until ? CarbonImmutable::parse($this->valid_until) : null,
            taxRate: (float) $this->tax_rate,
        ), auth()->user()->company_id);

        session()->flash('status', 'Estimate created.');

        $this->redirect(route('estimates.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Estimate" description="Build a quotation for a client." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-4">
                    <x-input-label for="title" value="Title" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="title" id="title" />
                    </div>
                    <x-input-error :messages="$errors->get('title')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="project_id" value="Project (optional)" />
                    <div class="mt-2">
                        <x-select id="project_id" wire:model="project_id">
                            <option value="">No project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('project_id')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="valid_until" value="Valid Until" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="valid_until" id="valid_until" />
                    </div>
                    <x-input-error :messages="$errors->get('valid_until')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="tax_rate" value="Tax Rate (%)" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" wire:model="tax_rate" id="tax_rate" />
                    </div>
                    <x-input-error :messages="$errors->get('tax_rate')" />
                </div>

                <div class="col-span-full" x-data="{ items: @entangle('items') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Line Items</h3>
                        <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: '0.00' })" class="text-sm font-semibold text-brand-600 hover:text-brand-500">Add item</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-6">
                                    <x-text-input x-model="item.description" placeholder="Description" />
                                </div>
                                <div class="col-span-2">
                                    <x-text-input type="number" x-model="item.quantity" min="1" placeholder="Qty" />
                                </div>
                                <div class="col-span-3">
                                    <x-text-input type="number" step="0.01" x-model="item.unit_price" placeholder="Unit price" />
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
            <a href="{{ route('estimates.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
