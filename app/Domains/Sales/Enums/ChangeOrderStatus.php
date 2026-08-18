<?php

declare(strict_types=1);

namespace App\Domains\Sales\Enums;

enum ChangeOrderStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
