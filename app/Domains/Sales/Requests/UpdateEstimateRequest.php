<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

class UpdateEstimateRequest extends StoreEstimateRequest
{
    public function authorize(): bool
    {
        $estimate = $this->route('estimate');

        return $this->user()?->can('update', $estimate) ?? false;
    }
}
