<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\CreateBoqItemAction;
use App\Domains\Finance\DTOs\BoqItemData;
use App\Domains\Finance\Models\CostCode;
use App\Domains\ProjectManagement\Models\Project;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('nullable|integer|exists:cost_codes,id')]
    public string $cost_code_id = '';

    #[Validate('nullable|string|max:50')]
    public string $item_code = '';

    #[Validate('required|string|max:255')]
    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public string $quantity = '';

    #[Validate('required|string|max:50')]
    public string $unit = 'lot';

    #[Validate('required|numeric|min:0')]
    public string $unit_price = '';

    public function with(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'projects' => Project::where('company_id', $companyId)->latest()->get(),
            'costCodes' => CostCode::where('company_id', $companyId)->orderBy('code')->get(),
        ];
    }

    private function companyCurrency(): string
    {
        return auth()->user()->company?->currency?->value ?? 'PHP';
    }

    public function save(CreateBoqItemAction $action): void
    {
        $this->validate();

        $dto = new BoqItemData(
            projectId: (int) $this->project_id,
            description: $this->description,
            quantity: (float) $this->quantity,
            unit: $this->unit,
            unitPrice: Money::fromDecimal((float) $this->unit_price, $this->companyCurrency()),
            costCodeId: $this->cost_code_id ? (int) $this->cost_code_id : null,
            itemCode: $this->item_code ?: null,
        );

        $action->execute($dto, auth()->user()->company_id);

        session()->flash('status', 'BOQ item created.');

        $this->redirect(route('boq_items.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
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
                    <label for="cost_code_id" class="block text-sm font-medium leading-6 text-gray-900">Cost Code</label>
                    <div class="mt-2">
                        <select id="cost_code_id" wire:model="cost_code_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">No cost code</option>
                            @foreach ($costCodes as $costCode)
                                <option value="{{ $costCode->id }}">{{ $costCode->code }} — {{ $costCode->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('cost_code_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="item_code" class="block text-sm font-medium leading-6 text-gray-900">Item Code</label>
                    <div class="mt-2">
                        <input type="text" wire:model="item_code" id="item_code" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('item_code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-full">
                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                    <div class="mt-2">
                        <input type="text" wire:model="description" id="description" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="quantity" class="block text-sm font-medium leading-6 text-gray-900">Quantity</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" min="0" wire:model="quantity" id="quantity" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('quantity') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="unit" class="block text-sm font-medium leading-6 text-gray-900">Unit</label>
                    <div class="mt-2">
                        <input type="text" wire:model="unit" id="unit" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('unit') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="unit_price" class="block text-sm font-medium leading-6 text-gray-900">Unit Price</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" min="0" wire:model="unit_price" id="unit_price" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('unit_price') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('boq_items.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
