<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Illuminate\Database\Seeder;

class ProgressReportSeeder extends Seeder
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

        $report = ProgressReport::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => ProgressReportStatus::Approved,
        ]);

        $report->items()->createMany([
            ['description' => 'Foundation works', 'quantity' => 1, 'percent_complete' => 60, 'accomplished_amount' => 1_500_000],
            ['description' => 'Structural works', 'quantity' => 1, 'percent_complete' => 25, 'accomplished_amount' => 1_000_000],
        ]);
    }
}
