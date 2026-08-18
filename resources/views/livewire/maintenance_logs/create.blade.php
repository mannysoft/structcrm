<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
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
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="equipment_id" class="block text-sm font-medium leading-6 text-gray-900">Equipment</label>
                    <div class="mt-2">
                        <select id="equipment_id" wire:model="equipment_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">Select equipment</option>
                            @foreach ($equipment as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('equipment_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="service_date" class="block text-sm font-medium leading-6 text-gray-900">Service Date</label>
                    <div class="mt-2">
                        <input type="date" wire:model="service_date" id="service_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('service_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                    <div class="mt-2">
                        <input type="text" wire:model="description" id="description" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="cost" class="block text-sm font-medium leading-6 text-gray-900">Cost (USD)</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" wire:model="cost" id="cost" min="0" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('cost') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('maintenance_logs.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
