<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
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

        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total' => 250000,
        ]);

        $invoice->items()->createMany([
            [
                'description' => 'Foundation work',
                'quantity' => 1,
                'unit_price' => 150000,
                'total' => 150000,
            ],
            [
                'description' => 'Framing',
                'quantity' => 1,
                'unit_price' => 100000,
                'total' => 100000,
            ],
        ]);
    }
}
