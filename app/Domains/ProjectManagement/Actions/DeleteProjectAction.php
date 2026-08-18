<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Support\Facades\DB;

final class DeleteProjectAction
{
    public function execute(Project $project): void
    {
        DB::transaction(function () use ($project) {
            $project->delete();
        });
    }
}
