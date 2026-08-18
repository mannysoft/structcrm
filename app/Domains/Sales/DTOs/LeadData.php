<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

use App\Domains\Sales\Enums\LeadStatus;

final readonly class LeadData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public LeadStatus $status,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $notes = null,
    ) {}
}
