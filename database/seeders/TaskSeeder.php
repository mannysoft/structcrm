<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\Sales\Models\Client;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::factory()->create([
            'name' => 'ConstructFlow Demo Builders',
        ]);

        $client = Client::factory()->create([
            'company_id' => $company->id,
        ]);

        $project = Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        Task::factory()->count(5)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);
    }
}
