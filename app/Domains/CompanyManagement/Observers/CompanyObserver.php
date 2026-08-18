<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Observers;

use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Support\Str;

class CompanyObserver
{
    public function creating(Company $company): void
    {
        if (empty($company->slug)) {
            $company->slug = $this->uniqueSlug($company->name);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (Company::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
