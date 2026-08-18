<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Observers;

use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Support\Str;

class ProjectObserver
{
    public function creating(Project $project): void
    {
        if (empty($project->uuid)) {
            $project->uuid = (string) Str::uuid();
        }
    }
}
