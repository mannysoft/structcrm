<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::factory()->create([
            'name' => 'ConstructFlow Demo Builders',
            'email' => 'hello@constructflow.test',
        ]);
    }
}
