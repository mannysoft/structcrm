<?php

declare(strict_types=1);

namespace App\Domains\Sales\Enums;

enum ChangeOrderItemType: string
{
    case Material = 'material';
    case Labor = 'labor';
    case Equipment = 'equipment';
    case Overhead = 'overhead';
    case Profit = 'profit';
    case Other = 'other';
}
