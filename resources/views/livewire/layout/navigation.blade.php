<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<nav x-data="{ open: false }">
    <!-- Mobile top bar -->
    <div class="fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 lg:hidden">
        <a href="{{ route('dashboard') }}" wire:navigate>
            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
        </a>

        <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Backdrop -->
    <div x-show="open" @click="open = false" class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden" x-cloak></div>

    <!-- Sidebar -->
    <aside
        x-cloak
        class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out lg:translate-x-0"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Logo -->
        <div class="flex h-16 shrink-0 items-center border-b border-gray-200 px-4">
            <a href="{{ route('dashboard') }}" wire:navigate>
                <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
            </a>
        </div>

        <!-- Navigation links -->
        <div class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-nav-link>
            <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" wire:navigate>
                {{ __('Projects') }}
            </x-nav-link>
            <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')" wire:navigate>
                {{ __('Tasks') }}
            </x-nav-link>
            <x-nav-link :href="route('daily_logs.index')" :active="request()->routeIs('daily_logs.*')" wire:navigate>
                {{ __('Daily Logs') }}
            </x-nav-link>
            <x-nav-link :href="route('project_photos.index')" :active="request()->routeIs('project_photos.*')" wire:navigate>
                {{ __('Site Photos') }}
            </x-nav-link>
            <x-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.*')" wire:navigate>
                {{ __('Documents') }}
            </x-nav-link>
            <x-nav-link :href="route('time_entries.index')" :active="request()->routeIs('time_entries.*')" wire:navigate>
                {{ __('Time') }}
            </x-nav-link>
            <x-nav-link :href="route('equipment.index')" :active="request()->routeIs('equipment.*')" wire:navigate>
                {{ __('Equipment') }}
            </x-nav-link>
            <x-nav-link :href="route('maintenance_logs.index')" :active="request()->routeIs('maintenance_logs.*')" wire:navigate>
                {{ __('Maintenance') }}
            </x-nav-link>
            <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" wire:navigate>
                {{ __('Clients') }}
            </x-nav-link>
            <x-nav-link :href="route('leads.index')" :active="request()->routeIs('leads.*')" wire:navigate>
                {{ __('Leads') }}
            </x-nav-link>
            <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')" wire:navigate>
                {{ __('Suppliers') }}
            </x-nav-link>
            <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')" wire:navigate>
                {{ __('Expenses') }}
            </x-nav-link>
            <x-nav-link :href="route('change_orders.index')" :active="request()->routeIs('change_orders.*')" wire:navigate>
                {{ __('Change Orders') }}
            </x-nav-link>
            <x-nav-link :href="route('purchase_orders.index')" :active="request()->routeIs('purchase_orders.*')" wire:navigate>
                {{ __('Purchase Orders') }}
            </x-nav-link>
            <x-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" wire:navigate>
                {{ __('Invoices') }}
            </x-nav-link>
            <x-nav-link :href="route('estimates.index')" :active="request()->routeIs('estimates.*')" wire:navigate>
                {{ __('Estimates') }}
            </x-nav-link>
            <x-nav-link :href="route('companies.index')" :active="request()->routeIs('companies.*')" wire:navigate>
                {{ __('Company') }}
            </x-nav-link>
            @can('manage_users')
                <x-nav-link :href="route('team.index')" :active="request()->routeIs('team.*')" wire:navigate>
                    {{ __('Team') }}
                </x-nav-link>
            @endcan
        </div>

        <!-- User section -->
        <div class="shrink-0 border-t border-gray-200 p-3">
            <div class="px-3 py-2">
                <div class="truncate text-sm font-medium text-gray-900" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <x-nav-link :href="route('profile')" :active="request()->routeIs('profile')" wire:navigate>
                {{ __('Profile') }}
            </x-nav-link>

            <button wire:click="logout" class="block w-full border-l-4 border-transparent ps-3 pe-4 py-2 text-start text-sm font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800 focus:outline-none">
                {{ __('Log Out') }}
            </button>
        </div>
    </aside>
</nav>
