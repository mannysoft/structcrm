<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\DTOs;

final readonly class RegisterTenantData
{
    public function __construct(
        public string $companyName,
        public string $userName,
        public string $email,
        public string $password,
    ) {}
}
