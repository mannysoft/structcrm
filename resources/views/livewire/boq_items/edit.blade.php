<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\UpdateBoqItemAction;
use App\Domains\Finance\DTOs\BoqItemData;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\CostCode;
use App\Domains\ProjectManagement\Models\Project;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

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

    public function mount(BoqItem $boqItem): void
    {
        $this->uuid = $boqItem->uuid;
        $this->project_id = (string) $boqItem->project_id;
        $this->cost_code_id = $boqItem->cost_code_id ? (string) $boqItem->cost_code_id : '';
        $this->item_code = $boqItem->item_code ?? '';
        $this->description = $boqItem->description;
        $this->quantity = (string) $boqItem->quantity;
        $this->unit = $boqItem->unit;
        $this->unit_price = (string) $boqItem->unitPrice->toDecimal();
    }

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

    public function save(UpdateBoqItemAction $action): void
    {
        $this->validate();

        $boqItem = BoqItem::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($boqItem, 404);

        $dto = new BoqItemData(
            projectId: (int) $this->project_id,
            description: $this->description,
            quantity: (float) $this->quantity,
            unit: $this->unit,
            unitPrice: Money::fromDecimal((float) $this->unit_price, $this->companyCurrency()),
            costCodeId: $this->cost_code_id ? (int) $this->cost_code_id : null,
            itemCode: $this->item_code ?: null,
        );

        $action->execute($boqItem, $dto);

        session()->flash('status', 'BOQ item updated.');

        $this->redirect(route('boq_items.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Edit BOQ Item" description="Update this bill of quantities line item." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
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
                    <x-input-label for="cost_code_id" value="Cost Code" />
                    <div class="mt-2">
                        <x-select id="cost_code_id" wire:model="cost_code_id">
                            <option value="">No cost code</option>
                            @foreach ($costCodes as $costCode)
                                <option value="{{ $costCode->id }}">{{ $costCode->code }} — {{ $costCode->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('cost_code_id')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="item_code" value="Item Code" />
                    <div class="mt-2">
                        <x-text-input wire:model="item_code" id="item_code" type="text" />
                    </div>
                    <x-input-error :messages="$errors->get('item_code')" />
                </div>

                <div class="col-span-full">
                    <x-input-label for="description" value="Description" />
                    <div class="mt-2">
                        <x-text-input wire:model="description" id="description" type="text" />
                    </div>
                    <x-input-error :messages="$errors->get('description')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="quantity" value="Quantity" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" min="0" wire:model="quantity" id="quantity" />
                    </div>
                    <x-input-error :messages="$errors->get('quantity')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="unit" value="Unit" />
                    <div class="mt-2">
                        <x-text-input wire:model="unit" id="unit" type="text" />
                    </div>
                    <x-input-error :messages="$errors->get('unit')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="unit_price" value="Unit Price" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" min="0" wire:model="unit_price" id="unit_price" />
                    </div>
                    <x-input-error :messages="$errors->get('unit_price')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('boq_items.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
