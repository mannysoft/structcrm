<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\DTOs;

use App\Domains\CompanyManagement\Enums\Currency;

final readonly class CompanyData
{
    public function __construct(
        public string $name,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $taxNumber = null,
        public ?string $timezone = null,
        public Currency $currency = Currency::USD,
        public ?string $logoPath = null,
    ) {}
}
