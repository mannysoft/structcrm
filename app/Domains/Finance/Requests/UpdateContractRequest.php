<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

class UpdateContractRequest extends StoreContractRequest
{
    public function authorize(): bool
    {
        $contract = $this->route('contract');

        return $this->user()?->can('update', $contract) ?? false;
    }
}
