<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\DTOs;

use Carbon\CarbonImmutable;

final readonly class TimeEntryData
{
    public function __construct(
        public int $projectId,
        public CarbonImmutable $clockInAt,
        public ?CarbonImmutable $clockOutAt = null,
        public int $breakMinutes = 0,
        public ?string $notes = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {}
}
