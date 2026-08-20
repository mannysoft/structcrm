<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\Sales\Actions\InviteClientUserAction;
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

    #[Validate('required|email|max:255')]
    public string $portalEmail = '';

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

    public function invite(InviteClientUserAction $action): void
    {
        $this->validateOnly('portalEmail');

        $client = Client::where('uuid', $this->uuid)->first();

        abort_unless($client, 404);

        $action->execute($client, $this->portalEmail);

        $this->reset('portalEmail');

        session()->flash('status', 'Portal invitation sent.');
    }
}; ?>

<div>
    <x-page-header title="Edit Client" description="Update client details and portal access." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-3">
                    <x-input-label for="name" value="Name" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="name" id="name" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="company_name" value="Company Name" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="company_name" id="company_name" />
                    </div>
                    <x-input-error :messages="$errors->get('company_name')" />
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
            <a href="{{ route('clients.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>

    <div class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <h2 class="text-base font-semibold leading-6 text-gray-900">Portal access</h2>
            <p class="mt-1 text-sm text-gray-500">Invite this client to the client portal. They'll receive an email with a secure link to set their password.</p>

            <form wire:submit="invite" class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input-label for="portalEmail" value="Email" />
                    <div class="mt-2">
                        <x-text-input type="email" wire:model="portalEmail" id="portalEmail" />
                    </div>
                    <x-input-error :messages="$errors->get('portalEmail')" />
                </div>
                <x-button type="submit">Send invitation</x-button>
            </form>
        </div>
    </div>
</div>
