<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Actions;

use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Support\Facades\DB;

final class DeleteCompanyAction
{
    public function execute(Company $company): void
    {
        DB::transaction(function () use ($company) {
            $company->delete();
        });
    }
}
