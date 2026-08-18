<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Support\Facades\DB;

final class UpdateProjectAction
{
    public function execute(Project $project, ProjectData $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update([
                'client_id' => $data->clientId,
                'title' => $data->title,
                'description' => $data->description,
                'status' => $data->status,
                'budget_amount' => $data->budget->amountInCents,
                'budget_currency' => $data->budget->currency,
                'manager_id' => $data->managerId,
                'start_date' => $data->startDate,
                'end_date' => $data->endDate,
            ]);

            return $project;
        });
    }
}
