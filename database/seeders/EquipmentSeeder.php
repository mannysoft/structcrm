<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::factory()->create([
            'name' => 'ConstructFlow Demo Builders',
        ]);

        $equipment = Equipment::factory()->count(3)->create([
            'company_id' => $company->id,
        ]);

        $equipment->each(function (Equipment $item) {
            MaintenanceLog::factory()->count(2)->create([
                'company_id' => $item->company_id,
                'equipment_id' => $item->id,
            ]);
        });
    }
}
