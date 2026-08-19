<?php

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;

it('logs in and reaches the dashboard', function () {
    $this->seed(RolePermissionSeeder::class);

    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => Hash::make('password'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')
            ->assertSee('Log in')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('Log in')
            ->waitForLocation('/dashboard')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');
    });
});
