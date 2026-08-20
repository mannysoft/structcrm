<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTOs;

use App\Domains\Finance\Enums\ProgressReportStatus;
use Carbon\CarbonImmutable;

final readonly class ProgressReportData
{
    /**
     * @param  array<int, array{description: string, quantity: float, percent_complete: float, accomplished_amount: int, boq_item_id?: ?int, cost_code_id?: ?int}>  $items
     */
    public function __construct(
        public int $projectId,
        public CarbonImmutable $reportDate,
        public ProgressReportStatus $status = ProgressReportStatus::Draft,
        public ?string $notes = null,
        public array $items = [],
    ) {}
}
