<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Requests;

class UpdateCompanyRequest extends StoreCompanyRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $this->user()?->can('update', $company) ?? false;
    }
}
