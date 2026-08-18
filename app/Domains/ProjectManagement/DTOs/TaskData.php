<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\DTOs;

use App\Domains\ProjectManagement\Enums\TaskPriority;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use Carbon\CarbonImmutable;

final readonly class TaskData
{
    public function __construct(
        public string $title,
        public int $projectId,
        public TaskStatus $status = TaskStatus::ToDo,
        public TaskPriority $priority = TaskPriority::Medium,
        public ?string $description = null,
        public ?int $assignedUserId = null,
        public ?CarbonImmutable $dueDate = null,
    ) {}
}
