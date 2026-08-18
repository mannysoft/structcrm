<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\DTOs;

use Carbon\CarbonImmutable;

final readonly class DailyLogData
{
    public function __construct(
        public int $projectId,
        public CarbonImmutable $logDate,
        public string $workPerformed,
        public ?string $weather = null,
        public int $crewCount = 0,
        public float $laborHours = 0.0,
        public ?string $equipmentUsage = null,
        public ?string $issues = null,
        public ?string $safetyIncidents = null,
    ) {}
}
