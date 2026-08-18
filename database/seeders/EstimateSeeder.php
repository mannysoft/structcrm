<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Estimate;
use Illuminate\Database\Seeder;

class EstimateSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::factory()->create([
            'name' => 'ConstructFlow Demo Builders',
        ]);

        $estimate = Estimate::factory()->create([
            'company_id' => $company->id,
            'subtotal' => 90000,
            'tax_amount' => 9000,
            'amount' => 99000,
        ]);

        $estimate->items()->createMany([
            [
                'description' => 'Demolition',
                'quantity' => 1,
                'unit_price' => 40000,
                'total' => 40000,
            ],
            [
                'description' => 'Drywall',
                'quantity' => 1,
                'unit_price' => 50000,
                'total' => 50000,
            ],
        ]);
    }
}
