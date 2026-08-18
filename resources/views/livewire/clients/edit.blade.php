<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\Sales\DTOs\ClientData;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Services\ClientService;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public string $company_name = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:50')]
    public string $phone = '';

    #[Validate('nullable|string|max:500')]
    public string $address = '';

    #[Validate('nullable|string')]
    public string $notes = '';

    public function mount(Client $client): void
    {
        $this->uuid = $client->uuid;
        $this->name = $client->name;
        $this->company_name = $client->company_name ?? '';
        $this->email = $client->email ?? '';
        $this->phone = $client->phone ?? '';
        $this->address = $client->address ?? '';
        $this->notes = $client->notes ?? '';
    }

    public function save(ClientService $service): void
    {
        $this->validate();

        $client = $service->find($this->uuid, auth()->user()->company_id);

        abort_unless($client, 404);

        $dto = new ClientData(
            name: $this->name,
            companyName: $this->company_name ?: null,
            email: $this->email ?: null,
            phone: $this->phone ?: null,
            address: $this->address ?: null,
            notes: $this->notes ?: null,
        );

        $service->update($client, $dto);

        session()->flash('status', 'Client updated.');

        $this->redirect(route('clients.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-3">
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Name</label>
                    <div class="mt-2">
                        <input type="text" wire:model="name" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="company_name" class="block text-sm font-medium leading-6 text-gray-900">Company Name</label>
                    <div class="mt-2">
                        <input type="text" wire:model="company_name" id="company_name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('company_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                    <div class="mt-2">
                        <input type="email" wire:model="email" id="email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Phone</label>
                    <div class="mt-2">
                        <input type="text" wire:model="phone" id="phone" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('phone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-full">
                    <label for="address" class="block text-sm font-medium leading-6 text-gray-900">Address</label>
                    <div class="mt-2">
                        <textarea id="address" wire:model="address" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('address') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
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
            <a href="{{ route('clients.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
