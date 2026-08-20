<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTOs;

use App\Domains\Finance\Enums\BillingStatus;
use Carbon\CarbonImmutable;

final readonly class BillingData
{
    /**
     * @param  array<int, array{description: string, quantity: float, unit_price: int, boq_item_id?: ?int}>  $items
     */
    public function __construct(
        public int $projectId,
        public ?int $progressReportId,
        public CarbonImmutable $periodStart,
        public CarbonImmutable $periodEnd,
        public BillingStatus $status = BillingStatus::Draft,
        public ?float $retentionRate = null,
        public int $otherDeductions = 0,
        public ?CarbonImmutable $dueDate = null,
        public ?string $notes = null,
        public string $currency = 'PHP',
        public array $items = [],
    ) {}
}
