<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'company' => auth()->user()?->company,
        ];
    }
}; ?>

<div>
    <x-page-header title="Company" description="Your company profile and billing settings.">
        <x-slot name="actions">
            <x-button :href="route('companies.settings')">Edit settings</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        @if ($company)
            <div class="px-4 py-6 sm:p-8">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->name }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->email ?? 'N/A' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->phone ?? 'N/A' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Tax Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->tax_number ?? 'N/A' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->timezone ?? 'N/A' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Currency</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->currency?->value ?? 'USD' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->address ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
        @else
            <div class="px-3 py-8 text-sm text-center text-gray-500">
                No company found. Create one to get started.
            </div>
        @endif
    </div>
</div>
