<?php

use Laravel\Dusk\Browser;

it('updates company settings', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/companies/settings')
            ->waitForLivewireToLoad()
            ->type('#name', 'Updated Construct Co.')
            ->type('#email', 'hello@construct.test')
            ->waitForLivewire()->press('Save')
            ->waitForLocation('/companies')
            ->assertPathIs('/companies');
    });
});
