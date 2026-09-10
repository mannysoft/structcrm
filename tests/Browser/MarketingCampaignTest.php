<?php

use Laravel\Dusk\Browser;

it('creates a marketing campaign with an email step', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/marketing/campaigns/create')
            ->waitForLivewireToLoad()
            ->type('#name', 'New Client Onboarding')
            ->addRepeaterItem('Add email', [
                'input[x-model="step.subject"]' => 'Welcome aboard',
                'input[x-model="step.delay_days"]' => '0',
                'textarea[x-model="step.body"]' => '<p>Hello {name}!</p>',
            ])
            ->waitForLivewire()->press('Save')
            ->waitForLocation('/marketing/campaigns')
            ->assertPathIs('/marketing/campaigns');
    });
});
