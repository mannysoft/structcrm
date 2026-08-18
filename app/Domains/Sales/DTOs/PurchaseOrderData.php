<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

use App\Domains\Sales\Enums\PurchaseOrderStatus;

final readonly class PurchaseOrderData
{
    /**
     * @param  array<int, array{description: string, quantity: int, unit_price: int}>  $items
     */
    public function __construct(
        public int $projectId,
        public int $supplierId,
        public PurchaseOrderStatus $status = PurchaseOrderStatus::Draft,
        public array $items = [],
    ) {}
}
