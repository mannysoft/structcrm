<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
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

        $supplier = Supplier::factory()->create([
            'company_id' => $company->id,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'total' => 150000,
        ]);

        $purchaseOrder->items()->createMany([
            [
                'description' => 'Concrete mix',
                'quantity' => 10,
                'unit_price' => 10000,
                'total' => 100000,
            ],
            [
                'description' => 'Rebar',
                'quantity' => 5,
                'unit_price' => 10000,
                'total' => 50000,
            ],
        ]);
    }
}
