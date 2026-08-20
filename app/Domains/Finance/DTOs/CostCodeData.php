<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTOs;

use App\Domains\Finance\Enums\CostCodeType;

final readonly class CostCodeData
{
    public function __construct(
        public string $code,
        public string $name,
        public CostCodeType $type = CostCodeType::Other,
        public ?int $parentId = null,
    ) {}
}
