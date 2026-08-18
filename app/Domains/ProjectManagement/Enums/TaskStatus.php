<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Enums;

enum TaskStatus: string
{
    case ToDo = 'to_do';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Done = 'done';
}
