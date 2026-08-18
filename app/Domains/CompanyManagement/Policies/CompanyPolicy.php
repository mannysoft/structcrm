<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Policies;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Company $company): bool
    {
        return $user->company_id === $company->id;
    }

    public function create(User $user): bool
    {
        return $user->company_id === null;
    }

    public function update(User $user, Company $company): bool
    {
        // Add more granular role checks here in the future
        return $user->company_id === $company->id;
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->company_id === $company->id;
    }
}
