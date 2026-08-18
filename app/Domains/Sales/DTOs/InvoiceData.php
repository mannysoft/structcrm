<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

use App\Domains\Sales\Enums\InvoiceStatus;
use Carbon\CarbonImmutable;

final readonly class InvoiceData
{
    /**
     * @param  array<int, array{description: string, quantity: int, unit_price: int}>  $items
     */
    public function __construct(
        public int $projectId,
        public InvoiceStatus $status,
        public CarbonImmutable $issueDate,
        public CarbonImmutable $dueDate,
        public array $items = [],
    ) {}
}
