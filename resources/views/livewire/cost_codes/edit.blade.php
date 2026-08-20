<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\Finance\Actions\UpdateCostCodeAction;
use App\Domains\Finance\DTOs\CostCodeData;
use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\CostCode;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|string|max:50')]
    public string $code = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string')]
    public string $type = 'other';

    #[Validate('nullable|integer|exists:cost_codes,id')]
    public string $parent_id = '';

    public function mount(CostCode $costCode): void
    {
        $this->uuid = $costCode->uuid;
        $this->code = $costCode->code;
        $this->name = $costCode->name;
        $this->type = $costCode->type->value;
        $this->parent_id = $costCode->parent_id ? (string) $costCode->parent_id : '';
    }

    public function with(): array
    {
        return [
            'costCodes' => CostCode::where('id', '!=', $this->costCodeId())->orderBy('code')->get(),
        ];
    }

    private function costCodeId(): int
    {
        $costCode = CostCode::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        return $costCode?->id ?? 0;
    }

    public function save(UpdateCostCodeAction $action): void
    {
        $this->validate();

        $costCode = CostCode::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($costCode, 404);

        $dto = new CostCodeData(
            code: $this->code,
            name: $this->name,
            type: CostCodeType::from($this->type),
            parentId: $this->parent_id ? (int) $this->parent_id : null,
        );

        $action->execute($costCode, $dto);

        session()->flash('status', 'Cost code updated.');

        $this->redirect(route('cost_codes.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-3">
                    <label for="code" class="block text-sm font-medium leading-6 text-gray-900">Code</label>
                    <div class="mt-2">
                        <input type="text" wire:model="code" id="code" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Name</label>
                    <div class="mt-2">
                        <input type="text" wire:model="name" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="type" class="block text-sm font-medium leading-6 text-gray-900">Type</label>
                    <div class="mt-2">
                        <select id="type" wire:model="type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="material">Material</option>
                            <option value="labor">Labor</option>
                            <option value="equipment">Equipment</option>
                            <option value="subcontract">Subcontract</option>
                            <option value="overhead">Overhead</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    @error('type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="parent_id" class="block text-sm font-medium leading-6 text-gray-900">Parent</label>
                    <div class="mt-2">
                        <select id="parent_id" wire:model="parent_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">No parent</option>
                            @foreach ($costCodes as $costCode)
                                <option value="{{ $costCode->id }}">{{ $costCode->code }} — {{ $costCode->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('parent_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('cost_codes.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
