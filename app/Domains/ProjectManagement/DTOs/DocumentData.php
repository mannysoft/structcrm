<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\DTOs;

use App\Domains\ProjectManagement\Enums\DocumentCategory;

final readonly class DocumentData
{
    public function __construct(
        public int $projectId,
        public string $name,
        public DocumentCategory $category,
        public string $filePath,
    ) {}
}
