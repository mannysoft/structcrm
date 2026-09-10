<?php

use Laravel\Dusk\Browser;

it('registers a new company and user', function () {
    $email = fake()->unique()->safeEmail();

    $this->browse(function (Browser $browser) use ($email) {
        $browser->visit('/register')
            ->assertSee('REGISTER')
            ->type('#company_name', 'Dusk Builders')
            ->type('#name', 'Dusk Owner')
            ->type('#email', $email)
            ->type('#password', 'password')
            ->type('#password_confirmation', 'password')
            ->press('REGISTER')
            ->waitForLocation('/dashboard')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');
    });

    $this->assertDatabaseHas('companies', ['name' => 'Dusk Builders']);
    $this->assertDatabaseHas('users', ['email' => $email]);
});

it('logs out an authenticated staff user', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->press('Log Out')
            ->waitForLocation('/')
            ->assertPathIs('/');
    });
});
