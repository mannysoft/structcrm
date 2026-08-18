<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Actions;

use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Support\Facades\DB;

final class UpdateCompanyAction
{
    public function execute(Company $company, CompanyData $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            $company->update([
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address,
                'tax_number' => $data->taxNumber,
                'timezone' => $data->timezone,
                'currency' => $data->currency,
            ]);

            if ($data->logoPath !== null) {
                $company->update(['logo_path' => $data->logoPath]);
            }

            return $company;
        });
    }
}
