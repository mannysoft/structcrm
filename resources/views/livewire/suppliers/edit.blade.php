<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Domains\Sales\Actions\UpdateSupplierAction;
use App\Domains\Sales\DTOs\SupplierData;
use App\Domains\Sales\Models\Supplier;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:50')]
    public string $phone = '';

    #[Validate('nullable|string|max:500')]
    public string $address = '';

    #[Validate('nullable|string')]
    public string $notes = '';

    public function mount(Supplier $supplier): void
    {
        $this->uuid = $supplier->uuid;
        $this->name = $supplier->name;
        $this->email = $supplier->email ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->address = $supplier->address ?? '';
        $this->notes = $supplier->notes ?? '';
    }

    public function save(UpdateSupplierAction $action): void
    {
        $this->validate();

        $supplier = Supplier::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($supplier, 404);

        $action->execute($supplier, new SupplierData(
            name: $this->name,
            email: $this->email ?: null,
            phone: $this->phone ?: null,
            address: $this->address ?: null,
            notes: $this->notes ?: null,
        ));

        session()->flash('status', 'Supplier updated.');

        $this->redirect(route('suppliers.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Edit Supplier" description="Update supplier details." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-4">
                    <x-input-label for="name" value="Name" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="name" id="name" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="email" value="Email" />
                    <div class="mt-2">
                        <x-text-input type="email" wire:model="email" id="email" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="phone" value="Phone" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="phone" id="phone" />
                    </div>
                    <x-input-error :messages="$errors->get('phone')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="address" value="Address" />
                    <div class="mt-2">
                        <x-textarea id="address" wire:model="address" rows="2"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('address')" />
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
            <a href="{{ route('suppliers.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
