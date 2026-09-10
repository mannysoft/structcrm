<?php

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;

it('logs in and reaches the dashboard', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => Hash::make('password'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')
            ->waitForLivewireToLoad()
            ->assertSee('LOG IN')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->waitForLivewire()->press('LOG IN')
            ->waitForLocation('/dashboard')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');
    });
});
