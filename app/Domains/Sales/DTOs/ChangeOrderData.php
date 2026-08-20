<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

use App\Domains\Sales\Enums\ChangeOrderStatus;

final readonly class ChangeOrderData
{
    /**
     * @param  array<int, array{category: string, description: string, amount: int}>  $items
     */
    public function __construct(
        public int $projectId,
        public string $description,
        public int $amountInCents,
        public ChangeOrderStatus $status = ChangeOrderStatus::Pending,
        public string $amountCurrency = 'PHP',
        public array $items = [],
    ) {}
}
