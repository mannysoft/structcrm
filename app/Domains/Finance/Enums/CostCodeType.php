<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

enum CostCodeType: string
{
    case Material = 'material';
    case Labor = 'labor';
    case Equipment = 'equipment';
    case Subcontract = 'subcontract';
    case Overhead = 'overhead';
    case Other = 'other';
}
