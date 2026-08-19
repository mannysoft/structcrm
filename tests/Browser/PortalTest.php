<?php

use Laravel\Dusk\Browser;

it('redirects unauthenticated portal visits to the portal login', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/portal')
            ->waitForLocation('/portal/login')
            ->assertPathIs('/portal/login');
    });
});
