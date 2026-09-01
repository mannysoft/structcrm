<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillingPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $billing = $this->route('billing');

        return $this->user()?->can('update', $billing) ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
