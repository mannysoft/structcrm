<?php

use Laravel\Dusk\Browser;

it('creates a client', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/clients/create')
            ->waitForLivewireToLoad()
            ->assertSee('Name')
            ->type('#name', 'Acme Construction')
            ->type('#company_name', 'Acme Construction Co.')
            ->type('#email', 'acme@example.com')
            ->waitForLivewire()->press('Save')
            ->waitForLocation('/clients')
            ->assertPathIs('/clients')
            ->assertSee('Acme Construction');
    });
});

it('lists created clients', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/clients')
            ->waitForLivewireToLoad()
            ->assertSee('Clients')
            ->assertSee('No clients found');
    });
});
