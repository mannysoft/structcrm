<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Actions\CreateEquipmentAction;
use App\Domains\ProjectManagement\DTOs\EquipmentData;
use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|in:heavy_machinery,tool,vehicle,other')]
    public string $type = 'tool';

    #[Validate('required|in:available,in_use,maintenance,retired')]
    public string $status = 'available';

    #[Validate('nullable|date')]
    public string $service_due_at = '';

    #[Validate('nullable|string')]
    public string $notes = '';

    public function with(): array
    {
        return [
            'types' => EquipmentType::cases(),
            'statuses' => EquipmentStatus::cases(),
        ];
    }

    public function save(CreateEquipmentAction $action): void
    {
        $this->validate();

        $action->execute(new EquipmentData(
            name: $this->name,
            type: EquipmentType::from($this->type),
            status: EquipmentStatus::from($this->status),
            serviceDueAt: $this->service_due_at !== '' ? CarbonImmutable::parse($this->service_due_at) : null,
            notes: $this->notes ?: null,
        ), auth()->user()->company_id);

        session()->flash('status', 'Equipment added.');

        $this->redirect(route('equipment.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
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
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ ucfirst(str_replace('_', ' ', $t->value)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                    <div class="mt-2">
                        <select id="status" wire:model="status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            @foreach ($statuses as $s)
                                <option value="{{ $s->value }}">{{ ucfirst(str_replace('_', ' ', $s->value)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('status') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="service_due_at" class="block text-sm font-medium leading-6 text-gray-900">Service Due</label>
                    <div class="mt-2">
                        <input type="date" wire:model="service_due_at" id="service_due_at" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('service_due_at') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes</label>
                    <div class="mt-2">
                        <textarea id="notes" wire:model="notes" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('equipment.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
