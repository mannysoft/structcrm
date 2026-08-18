<?php

declare(strict_types=1);

namespace App\Domains\Sales\Enums;

enum EstimateStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
