<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Actions;

use App\Domains\CompanyManagement\DTOs\RegisterTenantData;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateCompanyAndUserAction
{
    public function execute(RegisterTenantData $data): User
    {
        return DB::transaction(function () use ($data) {
            $company = Company::create([
                'name' => $data->companyName,
            ]);

            $user = User::create([
                'company_id' => $company->id,
                'name' => $data->userName,
                'email' => $data->email,
                'password' => Hash::make($data->password),
            ]);

            return $user;
        });
    }
}
