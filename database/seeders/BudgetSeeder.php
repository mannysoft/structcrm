<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\Budget;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
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

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'type' => BudgetType::Original,
            'status' => BudgetStatus::Approved,
        ]);

        $budget->items()->createMany([
            ['description' => 'Materials', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 8_000_000, 'total' => 8_000_000],
            ['description' => 'Labor', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 3_000_000, 'total' => 3_000_000],
            ['description' => 'Equipment', 'quantity' => 1, 'unit' => 'lot', 'unit_price' => 1_000_000, 'total' => 1_000_000],
        ]);
    }
}
