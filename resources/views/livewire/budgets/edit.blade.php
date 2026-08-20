<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\UpdateBudgetAction;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\Budget;
use App\Domains\ProjectManagement\Models\Project;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|string')]
    public string $type = 'original';

    #[Validate('required|string')]
    public string $status = 'draft';

    #[Validate('nullable|numeric|min:0')]
    public string $forecast = '';

    public array $items = [];

    public function mount(Budget $budget): void
    {
        $budget->load('items');

        $this->uuid = $budget->uuid;
        $this->project_id = (string) $budget->project_id;
        $this->type = $budget->type->value;
        $this->status = $budget->status->value;
        $this->forecast = $budget->forecast() ? (string) $budget->forecast()->toDecimal() : '';
        $this->items = $budget->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (string) $item->quantity,
            'unit' => $item->unit,
            'unit_price' => (string) ($item->unit_price / 100),
        ])->all();
    }

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

    public function save(UpdateBudgetAction $action): void
    {
        $this->validate();

        $this->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $budget = Budget::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($budget, 404);

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

        $action->execute($budget, $dto);

        session()->flash('status', 'Budget updated.');

        $this->redirect(route('budgets.index'), navigate: true);
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
                    <label for="type" class="block text-sm font-medium leading-6 text-gray-900">Type</label>
                    <div class="mt-2">
                        <select id="type" wire:model="type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="original">Original</option>
                            <option value="revised">Revised</option>
                        </select>
                    </div>
                    @error('type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                    <div class="mt-2">
                        <select id="status" wire:model="status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                    @error('status') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="forecast" class="block text-sm font-medium leading-6 text-gray-900">Forecast to Complete</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" min="0" wire:model="forecast" id="forecast" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('forecast') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-full" x-data="{ items: @entangle('items') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Budget Items</h3>
                        <button type="button" @click="items.push({ description: '', quantity: 1, unit: 'lot', unit_price: '0.00' })" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Add item</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-5">
                                    <input type="text" x-model="item.description" placeholder="Description" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                                <div class="col-span-2">
                                    <input type="number" step="0.01" x-model="item.quantity" min="0" placeholder="Qty" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                                <div class="col-span-2">
                                    <input type="text" x-model="item.unit" placeholder="Unit" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                                <div class="col-span-2">
                                    <input type="number" step="0.01" x-model="item.unit_price" placeholder="Unit price" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <button type="button" @click="items.splice(index, 1)" class="text-sm text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    @error('items') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    @error('items.*.description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('budgets.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
