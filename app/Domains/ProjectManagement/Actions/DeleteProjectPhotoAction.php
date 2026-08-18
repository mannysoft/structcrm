<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Support\Facades\DB;

final class DeleteProjectPhotoAction
{
    public function execute(ProjectPhoto $photo): void
    {
        DB::transaction(function () use ($photo) {
            $photo->delete();
        });
    }
}
