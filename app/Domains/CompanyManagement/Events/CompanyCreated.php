<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Events;

use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanyCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Company $company
    ) {}
}
