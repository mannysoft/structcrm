<?php

declare(strict_types=1);

namespace App\Domains\Sales\DTOs;

use App\Common\ValueObjects\Money;
use App\Domains\Sales\Enums\ExpenseCategory;
use Carbon\CarbonImmutable;

final readonly class ExpenseData
{
    public function __construct(
        public int $projectId,
        public ExpenseCategory $category,
        public Money $amount,
        public CarbonImmutable $date,
        public ?string $receiptPath = null,
        public ?string $notes = null,
    ) {}
}
