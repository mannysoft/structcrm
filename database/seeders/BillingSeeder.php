<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
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

        $billing = Billing::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => BillingStatus::Submitted,
        ]);

        $billing->items()->createMany([
            ['description' => 'Foundation works', 'quantity' => 1, 'unit_price' => 1_500_000, 'total' => 1_500_000],
            ['description' => 'Structural works', 'quantity' => 1, 'unit_price' => 1_000_000, 'total' => 1_000_000],
        ]);
    }
}
