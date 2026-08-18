<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Actions;

use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Events\CompanyCreated;
use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Support\Facades\DB;

final class CreateCompanyAction
{
    public function execute(CompanyData $data): Company
    {
        return DB::transaction(function () use ($data) {
            $company = Company::create([
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address,
                'tax_number' => $data->taxNumber,
                'timezone' => $data->timezone,
                'currency' => $data->currency,
                'logo_path' => $data->logoPath,
            ]);

            CompanyCreated::dispatch($company);

            return $company;
        });
    }
}
