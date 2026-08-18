<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

use App\Domains\Sales\Enums\EstimateStatus;
use Carbon\CarbonImmutable;

final readonly class EstimateData
{
    /**
     * @param  array<int, array{description: string, quantity: int, unit_price: int}>  $items
     */
    public function __construct(
        public string $title,
        public EstimateStatus $status,
        public array $items = [],
        public ?int $leadId = null,
        public ?int $projectId = null,
        public ?CarbonImmutable $validUntil = null,
        public float $taxRate = 0.0,
    ) {}
}
