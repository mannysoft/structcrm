<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Demo staff users keyed by role. Every user logs in with "password".
     *
     * @var array<string, array{name: string, email: string}>
     */
    private const USERS = [
        'owner' => ['name' => 'Owner', 'email' => 'owner@example.com'],
        'administrator' => ['name' => 'Administrator', 'email' => 'admin@example.com'],
        'project_manager' => ['name' => 'Project Manager', 'email' => 'manager@example.com'],
        'engineer' => ['name' => 'Engineer', 'email' => 'engineer@example.com'],
        'foreman' => ['name' => 'Foreman', 'email' => 'foreman@example.com'],
        'staff' => ['name' => 'Staff Member', 'email' => 'staff@example.com'],
        'accountant' => ['name' => 'Accountant', 'email' => 'accountant@example.com'],
    ];

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $company = Company::factory()->create([
            'name' => 'ConstructFlow Demo Builders',
        ]);

        foreach (self::USERS as $role => $user) {
            User::factory()->role($role)->create([
                'company_id' => $company->id,
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
            ]);
        }
    }
}
