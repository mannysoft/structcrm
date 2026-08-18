<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Requests;

use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Company::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:64', Rule::in(timezone_identifiers_list())],
            'currency' => ['required', Rule::enum(Currency::class)],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function toDTO(): CompanyData
    {
        return new CompanyData(
            name: $this->validated('name'),
            email: $this->validated('email'),
            phone: $this->validated('phone'),
            address: $this->validated('address'),
            taxNumber: $this->validated('tax_number'),
            timezone: $this->validated('timezone'),
            currency: Currency::from($this->validated('currency')),
            logoPath: $this->storeLogo(),
        );
    }

    private function storeLogo(): ?string
    {
        if (! $this->hasFile('logo')) {
            return null;
        }

        return $this->file('logo')->store('company-logos');
    }
}
