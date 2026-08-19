<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\ClientUser;
use Illuminate\Database\Seeder;

class ClientUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::factory()->create([
            'name' => 'ConstructFlow Demo Builders',
        ]);

        Client::factory()->count(3)->create(['company_id' => $company->id])->each(function (Client $client): void {
            ClientUser::factory()->create([
                'company_id' => $company->id,
                'client_id' => $client->id,
                'email' => $client->email,
            ]);
        });
    }
}
