<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

enum BudgetStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
}
