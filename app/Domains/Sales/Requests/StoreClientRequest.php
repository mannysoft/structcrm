<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

use App\Domains\Sales\DTOs\ClientData;
use App\Domains\Sales\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Client::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): ClientData
    {
        return new ClientData(
            name: $this->validated('name'),
            companyName: $this->validated('company_name'),
            email: $this->validated('email'),
            phone: $this->validated('phone'),
            address: $this->validated('address'),
            notes: $this->validated('notes'),
        );
    }
}
