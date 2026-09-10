<?php

use Laravel\Dusk\Browser;

it('updates the profile information', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/profile')
            ->type('#name', 'Updated Name')
            ->press('SAVE')
            ->waitForText('Saved.');
    });
});
