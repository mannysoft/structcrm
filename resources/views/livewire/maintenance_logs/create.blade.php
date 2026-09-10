<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Actions\CreateMaintenanceLogAction;
use App\Domains\ProjectManagement\DTOs\MaintenanceLogData;
use App\Domains\ProjectManagement\Models\Equipment;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|integer|exists:equipment,id')]
    public string $equipment_id = '';

    #[Validate('required|date')]
    public string $service_date = '';

    #[Validate('required|string|max:255')]
    public string $description = '';

    #[Validate('nullable|numeric|min:0')]
    public string $cost = '0';

    public function with(): array
    {
        return [
            'equipment' => Equipment::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(CreateMaintenanceLogAction $action): void
    {
        $this->validate();

        $action->execute(new MaintenanceLogData(
            equipmentId: (int) $this->equipment_id,
            serviceDate: CarbonImmutable::parse($this->service_date),
            description: $this->description,
            cost: Money::fromDecimal((float) $this->cost),
        ), auth()->user()->company_id);

        session()->flash('status', 'Maintenance log created.');

        $this->redirect(route('maintenance_logs.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Log Service" description="Record a maintenance service." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <x-input-label for="equipment_id" value="Equipment" />
                    <div class="mt-2">
                        <x-select id="equipment_id" wire:model="equipment_id">
                            <option value="">Select equipment</option>
                            @foreach ($equipment as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('equipment_id')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="service_date" value="Service Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="service_date" id="service_date" />
                    </div>
                    <x-input-error :messages="$errors->get('service_date')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="description" value="Description" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="description" id="description" />
                    </div>
                    <x-input-error :messages="$errors->get('description')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="cost" value="Cost (USD)" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" min="0" wire:model="cost" id="cost" />
                    </div>
                    <x-input-error :messages="$errors->get('cost')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('maintenance_logs.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
