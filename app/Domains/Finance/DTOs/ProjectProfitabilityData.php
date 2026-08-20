<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTOs;

final readonly class ProjectProfitabilityData
{
    public function __construct(
        public int $projectId,
        public int $originalContractValue,
        public int $revisedContractValue,
        public int $originalBudget,
        public int $revisedBudget,
        public int $actualCost,
        public int $forecastToComplete,
        public int $projectedFinalCost,
        public int $projectedProfit,
        public float $projectedMargin,
        public int $costVariance,
        public string $currency,
    ) {}
}
