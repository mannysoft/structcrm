<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\DTOs;

use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use Carbon\CarbonImmutable;

final readonly class ProjectData
{
    public function __construct(
        public string $name,
        public ProjectStatus $status,
        public Money $budget,
        public ?CarbonImmutable $startDate = null,
        public ?CarbonImmutable $endDate = null,
    ) {}
}
