<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\ProjectPhotoData;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Support\Facades\DB;

final class UpdateProjectPhotoAction
{
    public function execute(ProjectPhoto $photo, ProjectPhotoData $data): ProjectPhoto
    {
        return DB::transaction(function () use ($photo, $data) {
            $photo->update([
                'project_id' => $data->projectId,
                'category' => $data->category,
                'file_path' => $data->filePath,
                'caption' => $data->caption,
            ]);

            return $photo;
        });
    }
}
