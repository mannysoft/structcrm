<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Enums;

enum DocumentCategory: string
{
    case Blueprint = 'blueprint';
    case Cad = 'cad';
    case Permit = 'permit';
    case Contract = 'contract';
    case Spec = 'spec';
}
