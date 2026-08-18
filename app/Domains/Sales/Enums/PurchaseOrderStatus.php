<?php

declare(strict_types=1);

namespace App\Domains\Sales\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Approved = 'approved';
    case Received = 'received';
}
