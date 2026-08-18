<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Enums;

enum PhotoCategory: string
{
    case Before = 'before';
    case During = 'during';
    case After = 'after';
    case Inspection = 'inspection';
    case Safety = 'safety';
}
