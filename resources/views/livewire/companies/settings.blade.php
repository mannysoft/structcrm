<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Domains\CompanyManagement\Actions\UpdateCompanyAction;
use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Models\Company;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|email|max:255')]
    public ?string $email = null;

    #[Validate('nullable|string|max:50')]
    public ?string $phone = null;

    #[Validate('nullable|string|max:500')]
    public ?string $address = null;

    #[Validate('nullable|string|max:100')]
    public ?string $tax_number = null;

    #[Validate('nullable|string|max:64')]
    public ?string $timezone = null;

    #[Validate('required|string')]
    public string $currency = 'USD';

    #[Validate('nullable|image|max:2048')]
    public $logo = null;

    public function mount(): void
    {
        $company = auth()->user()?->company;

        abort_unless($company instanceof Company, 403);

        $this->name = $company->name;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->address = $company->address;
        $this->tax_number = $company->tax_number;
        $this->timezone = $company->timezone;
        $this->currency = $company->currency->value ?? 'USD';
    }

    public function with(): array
    {
        return [
            'currencies' => Currency::cases(),
            'timezones' => timezone_identifiers_list(),
        ];
    }

    public function save(UpdateCompanyAction $action)
    {
        $this->validate();

        $company = auth()->user()?->company;

        abort_unless($company instanceof Company, 403);

        $dto = new CompanyData(
            name: $this->name,
            email: $this->email,
            phone: $this->phone,
            address: $this->address,
            taxNumber: $this->tax_number,
            timezone: $this->timezone,
            currency: Currency::from($this->currency),
            logoPath: $this->logo ? $this->logo->store('company-logos') : null,
        );

        $action->execute($company, $dto);

        session()->flash('status', 'Company settings saved.');

        $this->redirect('/companies', navigate: true);
    }
}; ?>

<div>
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-4">
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Company Name</label>
                    <div class="mt-2">
                        <input type="text" wire:model="name" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
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

                <div class="sm:col-span-3">
                    <label for="tax_number" class="block text-sm font-medium leading-6 text-gray-900">Tax Number</label>
                    <div class="mt-2">
                        <input type="text" wire:model="tax_number" id="tax_number" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('tax_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="currency" class="block text-sm font-medium leading-6 text-gray-900">Currency</label>
                    <div class="mt-2">
                        <select id="currency" wire:model="currency" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->value }}">{{ $currency->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('currency') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-4">
                    <label for="timezone" class="block text-sm font-medium leading-6 text-gray-900">Timezone</label>
                    <div class="mt-2">
                        <select id="timezone" wire:model="timezone" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}">{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('timezone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-4">
                    <label for="logo" class="block text-sm font-medium leading-6 text-gray-900">Logo</label>
                    <div class="mt-2">
                        <input type="file" wire:model="logo" id="logo" class="block w-full text-sm text-gray-900 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    @error('logo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-full">
                    <label for="address" class="block text-sm font-medium leading-6 text-gray-900">Address</label>
                    <div class="mt-2">
                        <textarea id="address" wire:model="address" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('address') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="/companies" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
