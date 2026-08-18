<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

use App\Common\ValueObjects\Money;
use App\Domains\Sales\Enums\EstimateStatus;
use Carbon\CarbonImmutable;

final readonly class EstimateData
{
    public function __construct(
        public string $title,
        public Money $amount,
        public EstimateStatus $status,
        public ?int $leadId = null,
        public ?int $projectId = null,
        public ?CarbonImmutable $validUntil = null,
    ) {}
}
