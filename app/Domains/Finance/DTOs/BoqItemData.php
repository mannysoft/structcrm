<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTOs;

use App\Common\ValueObjects\Money;

final readonly class BoqItemData
{
    public function __construct(
        public int $projectId,
        public string $description,
        public float $quantity,
        public string $unit,
        public Money $unitPrice,
        public ?int $costCodeId = null,
        public ?string $itemCode = null,
    ) {}
}
