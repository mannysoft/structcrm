<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domains\Sales\Enums\LeadStatus;
use App\Domains\Sales\DTOs\LeadData;
use App\Domains\Sales\Models\Lead;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Lead::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): LeadData
    {
        return new LeadData(
            firstName: $this->validated('first_name'),
            lastName: $this->validated('last_name'),
            status: LeadStatus::from($this->validated('status')),
            email: $this->validated('email'),
            phone: $this->validated('phone'),
            notes: $this->validated('notes'),
        );
    }
}
