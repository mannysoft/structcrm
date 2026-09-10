<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.guest')] class extends Component {
    public int $userId = 0;

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        $this->userId = $user->id;
    }

    public function setPassword(): void
    {
        $this->validate();

        $user = User::findOrFail($this->userId);

        $user->forceFill([
            'password' => $this->password,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Auth::login($user);

        session()->regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="setPassword">
        <h2 class="text-2xl font-semibold leading-6 text-gray-900">Set your password</h2>
        <p class="mt-2 text-sm text-gray-600">Choose a password to sign in to ConstructFlow.</p>

        <div class="mt-6">
            <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
            <div class="mt-2">
                <input type="password" wire:model="password" id="password" autofocus autocomplete="new-password" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6">
            </div>
            @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mt-4">
            <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirm password</label>
            <div class="mt-2">
                <input type="password" wire:model="password_confirmation" id="password_confirmation" autocomplete="new-password" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6">
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="flex w-full justify-center rounded-md bg-brand-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">Set password</button>
        </div>
    </form>
</div>
