<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\CreateBudgetAction;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\ProjectManagement\Models\Project;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|string')]
    public string $type = 'original';

    #[Validate('required|string')]
    public string $status = 'draft';

    #[Validate('nullable|numeric|min:0')]
    public string $forecast = '';

    public array $items = [];

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    private function companyCurrency(): string
    {
        return auth()->user()->company?->currency?->value ?? 'PHP';
    }

    public function save(CreateBudgetAction $action): void
    {
        $this->validate();

        $this->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $items = collect($this->items)->map(fn (array $item) => [
            'description' => $item['description'],
            'quantity' => (float) $item['quantity'],
            'unit' => $item['unit'],
            'unit_price' => (int) round((float) $item['unit_price'] * 100),
        ])->all();

        $dto = new BudgetData(
            projectId: (int) $this->project_id,
            type: BudgetType::from($this->type),
            status: BudgetStatus::from($this->status),
            forecast: $this->forecast !== '' ? Money::fromDecimal((float) $this->forecast, $this->companyCurrency()) : null,
            items: $items,
        );

        $action->execute($dto, auth()->user()->company_id);

        session()->flash('status', 'Budget created.');

        $this->redirect(route('budgets.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Budget" description="Create a project budget." />

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
                    <x-input-label for="type" value="Type" />
                    <div class="mt-2">
                        <x-select id="type" wire:model="type">
                            <option value="original">Original</option>
                            <option value="revised">Revised</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('type')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="forecast" value="Forecast to Complete" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" min="0" wire:model="forecast" id="forecast" />
                    </div>
                    <x-input-error :messages="$errors->get('forecast')" />
                </div>

                <div class="col-span-full" x-data="{ items: @entangle('items') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Budget Items</h3>
                        <button type="button" @click="items.push({ description: '', quantity: 1, unit: 'lot', unit_price: '0.00' })" class="text-sm font-semibold text-brand-600 hover:text-brand-500">Add item</button>
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
                                    <x-text-input x-model="item.unit" placeholder="Unit" />
                                </div>
                                <div class="col-span-2">
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
            <a href="{{ route('budgets.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
