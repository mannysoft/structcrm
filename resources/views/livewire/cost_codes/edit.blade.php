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
    <x-page-header title="Edit Cost Code" description="Update this cost code." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <x-input-label for="code" value="Code" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="code" id="code" />
                    </div>
                    <x-input-error :messages="$errors->get('code')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="name" value="Name" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="name" id="name" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="type" value="Type" />
                    <div class="mt-2">
                        <x-select id="type" wire:model="type">
                            <option value="material">Material</option>
                            <option value="labor">Labor</option>
                            <option value="equipment">Equipment</option>
                            <option value="subcontract">Subcontract</option>
                            <option value="overhead">Overhead</option>
                            <option value="other">Other</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('type')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="parent_id" value="Parent" />
                    <div class="mt-2">
                        <x-select id="parent_id" wire:model="parent_id">
                            <option value="">No parent</option>
                            @foreach ($costCodes as $costCode)
                                <option value="{{ $costCode->id }}">{{ $costCode->code }} — {{ $costCode->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('parent_id')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('cost_codes.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
