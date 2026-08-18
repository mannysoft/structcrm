<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Events\ProjectCreated;
use Illuminate\Support\Facades\DB;

final class CreateProjectAction
{
    public function execute(ProjectData $data, int $companyId): Project
    {
        return DB::transaction(function () use ($data, $companyId) {
            $project = Project::create([
                'company_id' => $companyId,
                'client_id' => $data->clientId,
                'project_number' => $data->projectNumber,
                'title' => $data->title,
                'description' => $data->description,
                'status' => $data->status,
                'budget_amount' => $data->budget->amountInCents,
                'budget_currency' => $data->budget->currency,
                'manager_id' => $data->managerId,
                'start_date' => $data->startDate,
                'end_date' => $data->endDate,
            ]);

            // Dispatch domain event for queues/listeners
            ProjectCreated::dispatch($project);

            return $project;
        });
    }
}
