<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\DTOs;

use App\Common\ValueObjects\Money;
use Carbon\CarbonImmutable;

final readonly class MaintenanceLogData
{
    public function __construct(
        public int $equipmentId,
        public CarbonImmutable $serviceDate,
        public string $description,
        public Money $cost,
    ) {}
}
