<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-6 text-gray-900">{{ __('Profile') }}</h1>
        <p class="mt-1 text-sm text-gray-500">Manage your account information and security settings.</p>
    </x-slot>

    <div class="space-y-6">
        <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-app-layout>
