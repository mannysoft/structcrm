<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

class UpdateBillingRequest extends StoreBillingRequest
{
    public function authorize(): bool
    {
        $billing = $this->route('billing');

        return $this->user()?->can('update', $billing) ?? false;
    }
}
