<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Domains\Sales\Actions\CreateLeadAction;
use App\Domains\Sales\DTOs\LeadData;
use App\Domains\Sales\Enums\LeadStatus;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $first_name = '';
    
    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:50')]
    public string $phone = '';
    
    #[Validate('required|string')]
    public string $status = 'new';
    
    #[Validate('nullable|string')]
    public string $notes = '';

    public function save(CreateLeadAction $action)
    {
        $this->validate();

        $dto = new LeadData(
            firstName: $this->first_name,
            lastName: $this->last_name,
            status: LeadStatus::from($this->status),
            email: $this->email ?: null,
            phone: $this->phone ?: null,
            notes: $this->notes ?: null,
        );
        
        $companyId = auth()->check() ? auth()->user()->company_id : 1; 

        $action->execute($dto, $companyId);

        session()->flash('status', 'Lead successfully created.');

        $this->redirect('/leads', navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Lead" description="Add a prospective client." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <x-input-label for="first_name" value="First Name" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="first_name" id="first_name" />
                    </div>
                    <x-input-error :messages="$errors->get('first_name')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="last_name" value="Last Name" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="last_name" id="last_name" />
                    </div>
                    <x-input-error :messages="$errors->get('last_name')" />
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
                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="qualified">Qualified</option>
                            <option value="lost">Lost</option>
                            <option value="converted">Converted</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
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
            <a href="/leads" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
