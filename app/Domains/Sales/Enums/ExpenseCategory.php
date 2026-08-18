<?php

declare(strict_types=1);

namespace App\Domains\Sales\Enums;

enum ExpenseCategory: string
{
    case Labor = 'labor';
    case Fuel = 'fuel';
    case Materials = 'materials';
    case Equipment = 'equipment';
    case Subcontract = 'subcontract';
}
