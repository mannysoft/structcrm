<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Observers;

use App\Domains\ProjectManagement\Models\Project;

class ProjectObserver
{
    public function creating(Project $project): void
    {
        if (empty($project->project_number)) {
            $project->project_number = $this->nextProjectNumber($project->company_id);
        }
    }

    private function nextProjectNumber(?int $companyId): string
    {
        $year = (string) now()->year;

        $count = Project::withTrashed()
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->count();

        return sprintf('PRJ-%s-%03d', $year, $count + 1);
    }
}
