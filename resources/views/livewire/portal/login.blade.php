<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.guest')] class extends Component {
    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public function mount(): void
    {
        if (Auth::guard('client')->check()) {
            $this->redirect(route('portal.index'), navigate: true);
        }
    }

    public function login(): void
    {
        $this->validate();

        $attempted = Auth::guard('client')->attempt([
            'email' => $this->email,
            'password' => $this->password,
            'type' => User::TYPE_CLIENT,
        ]);

        if (! $attempted) {
            $this->addError('email', 'These credentials do not match our records.');

            return;
        }

        session()->regenerate();

        $this->redirectIntended(route('portal.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="login">
        <h2 class="text-2xl font-semibold leading-6 text-gray-900">Client Portal</h2>
        <p class="mt-2 text-sm text-gray-600">Sign in to view your project progress, approve change orders, and manage invoices.</p>

        <div class="mt-6">
            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
            <div class="mt-2">
                <input type="email" wire:model="email" id="email" autofocus autocomplete="email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6">
            </div>
            @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mt-4">
            <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
            <div class="mt-2">
                <input type="password" wire:model="password" id="password" autocomplete="current-password" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6">
            </div>
            @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mt-6">
            <button type="submit" class="flex w-full justify-center rounded-md bg-brand-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">Sign in</button>
        </div>
    </form>
</div>
