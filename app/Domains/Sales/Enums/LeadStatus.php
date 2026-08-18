<?php

declare(strict_types=1);

namespace App\Domains\Sales\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Lost = 'lost';
    case Converted = 'converted';
}
