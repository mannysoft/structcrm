<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTOs;

use App\Common\ValueObjects\Money;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;

final readonly class BudgetData
{
    /**
     * @param  array<int, array{description: string, quantity: float, unit: string, unit_price: int, cost_code_id?: ?int, boq_item_id?: ?int}>  $items
     */
    public function __construct(
        public int $projectId,
        public BudgetType $type = BudgetType::Original,
        public BudgetStatus $status = BudgetStatus::Draft,
        public ?Money $forecast = null,
        public array $items = [],
    ) {}
}
