<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

use App\Domains\Sales\Enums\ChangeOrderStatus;

final readonly class ChangeOrderData
{
    public function __construct(
        public int $projectId,
        public string $description,
        public int $amountInCents,
        public ChangeOrderStatus $status = ChangeOrderStatus::Pending,
    ) {}
}
