<?php

use Laravel\Dusk\Browser;

it('updates the profile information', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/profile')
            ->waitForLivewireToLoad()
            ->type('#name', 'Updated Name')
            ->waitForLivewire()->press('SAVE')
            ->waitForText('Saved.');
    });
});
