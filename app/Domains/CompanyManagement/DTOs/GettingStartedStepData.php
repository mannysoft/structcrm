<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\DTOs;

final readonly class GettingStartedStepData
{
    /**
     * @param  array<int, string>  $missingDetails
     */
    public function __construct(
        public string $key,
        public string $title,
        public string $description,
        public string $url,
        public string $actionLabel,
        public bool $isCompleted,
        public ?string $requires = null,
        public array $missingDetails = [],
    ) {}
}
