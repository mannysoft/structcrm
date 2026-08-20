<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

enum ProgressReportStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
}
