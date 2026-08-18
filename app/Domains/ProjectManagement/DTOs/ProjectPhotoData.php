<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\DTOs;

use App\Domains\ProjectManagement\Enums\PhotoCategory;

final readonly class ProjectPhotoData
{
    public function __construct(
        public int $projectId,
        public PhotoCategory $category,
        public string $filePath,
        public ?string $caption = null,
    ) {}
}
