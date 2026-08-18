<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

final readonly class ClientData
{
    public function __construct(
        public string $name,
        public ?string $companyName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $notes = null,
    ) {}
}
