<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\ProjectPhotoData;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Support\Facades\DB;

final class CreateProjectPhotoAction
{
    public function execute(ProjectPhotoData $data, int $companyId, int $userId): ProjectPhoto
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            return ProjectPhoto::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'user_id' => $userId,
                'category' => $data->category,
                'file_path' => $data->filePath,
                'caption' => $data->caption,
            ]);
        });
    }
}
