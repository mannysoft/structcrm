<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
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
    <x-page-header title="Add Equipment" description="Track machinery, tools and vehicles." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
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
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ ucfirst(str_replace('_', ' ', $t->value)) }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('type')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            @foreach ($statuses as $s)
                                <option value="{{ $s->value }}">{{ ucfirst(str_replace('_', ' ', $s->value)) }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="service_due_at" value="Service Due" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="service_due_at" id="service_due_at" />
                    </div>
                    <x-input-error :messages="$errors->get('service_due_at')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="notes" value="Notes" />
                    <div class="mt-2">
                        <x-textarea id="notes" wire:model="notes" rows="3"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('equipment.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
