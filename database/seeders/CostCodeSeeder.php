<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Database\Seeder;

class CostCodeSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::factory()->create([
            'name' => 'ConstructFlow Demo Builders',
        ]);

        CostCode::factory()->create([
            'company_id' => $company->id,
            'code' => '100',
            'name' => 'Materials',
            'type' => CostCodeType::Material,
        ]);

        CostCode::factory()->create([
            'company_id' => $company->id,
            'code' => '200',
            'name' => 'Labor',
            'type' => CostCodeType::Labor,
        ]);

        CostCode::factory()->create([
            'company_id' => $company->id,
            'code' => '300',
            'name' => 'Equipment',
            'type' => CostCodeType::Equipment,
        ]);
    }
}
