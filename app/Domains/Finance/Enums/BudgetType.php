<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

enum BudgetType: string
{
    case Original = 'original';
    case Revised = 'revised';
}
