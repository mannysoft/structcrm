<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\CostCode;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Illuminate\Database\Seeder;

class BoqItemSeeder extends Seeder
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

        $material = CostCode::factory()->create([
            'company_id' => $company->id,
            'code' => '100',
            'name' => 'Materials',
            'type' => CostCodeType::Material,
        ]);

        BoqItem::factory()->count(5)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'cost_code_id' => $material->id,
        ]);
    }
}
