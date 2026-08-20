<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

enum ContractStatus: string
{
    case Draft = 'draft';
    case Signed = 'signed';
    case Completed = 'completed';
    case Terminated = 'terminated';
}
