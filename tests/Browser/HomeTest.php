<?php

use Laravel\Dusk\Browser;

it('shows the welcome page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Laravel');
    });
});

it('redirects guests to the login page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/dashboard')
            ->assertPathIs('/login')
            ->assertSee('LOG IN');
    });
});
