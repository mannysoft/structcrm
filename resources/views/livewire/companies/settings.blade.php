<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Domains\CompanyManagement\Actions\UpdateCompanyAction;
use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\CompanyManagement\Services\GettingStartedService;

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

    public function with(GettingStartedService $gettingStarted): array
    {
        $company = auth()->user()?->company;

        return [
            'currencies' => Currency::cases(),
            'timezones' => timezone_identifiers_list(),
            'gettingStartedProgress' => $company instanceof Company ? $gettingStarted->progress($company) : null,
        ];
    }

    public function dismissGettingStarted(GettingStartedService $gettingStarted): void
    {
        $company = auth()->user()?->company;

        if ($company instanceof Company) {
            $gettingStarted->dismiss($company);

            session()->flash('status', 'Getting started checklist hidden from the dashboard.');
        }
    }

    public function restoreGettingStarted(GettingStartedService $gettingStarted): void
    {
        $company = auth()->user()?->company;

        if ($company instanceof Company) {
            $gettingStarted->restore($company);

            session()->flash('status', 'Getting started checklist shown on the dashboard.');
        }
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
    <x-page-header title="Company Settings" description="Update your company profile and billing settings." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-4">
                    <x-input-label for="name" value="Company Name" />
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

                <div class="sm:col-span-3">
                    <x-input-label for="tax_number" value="Tax Number" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="tax_number" id="tax_number" />
                    </div>
                    <x-input-error :messages="$errors->get('tax_number')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="currency" value="Currency" />
                    <div class="mt-2">
                        <x-select id="currency" wire:model="currency">
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->value }}">{{ $currency->value }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('currency')" />
                </div>

                <div class="sm:col-span-4">
                    <x-input-label for="timezone" value="Timezone" />
                    <div class="mt-2">
                        <x-select id="timezone" wire:model="timezone">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}">{{ $timezone }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('timezone')" />
                </div>

                <div class="sm:col-span-4">
                    <x-input-label for="logo" value="Logo" />
                    <div class="mt-2">
                        <input type="file" wire:model="logo" id="logo" class="block w-full text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                    <x-input-error :messages="$errors->get('logo')" />
                </div>

                <div class="col-span-full">
                    <x-input-label for="address" value="Address" />
                    <div class="mt-2">
                        <x-textarea id="address" wire:model="address" rows="3"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('address')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('companies.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>

    @if ($gettingStartedProgress)
        <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-5 sm:px-8">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Getting started checklist</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        @if ($gettingStartedProgress->isComplete())
                            All setup steps are complete.
                        @elseif ($gettingStartedProgress->isDismissed)
                            {{ $gettingStartedProgress->completed() }} of {{ $gettingStartedProgress->total() }} steps complete · hidden from the dashboard.
                        @else
                            {{ $gettingStartedProgress->completed() }} of {{ $gettingStartedProgress->total() }} steps complete · shown on the dashboard.
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <x-button :href="route('getting_started.index')" variant="secondary" size="sm">View checklist</x-button>

                    @if ($gettingStartedProgress->isDismissed)
                        <x-button type="button" wire:click="restoreGettingStarted" size="sm">Show on dashboard</x-button>
                    @else
                        <x-button type="button" wire:click="dismissGettingStarted" variant="secondary" size="sm">Hide from dashboard</x-button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
