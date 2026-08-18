<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Enums;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case CAD = 'CAD';
    case AUD = 'AUD';
    case NZD = 'NZD';
}
