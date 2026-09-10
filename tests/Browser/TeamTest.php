<?php

use Laravel\Dusk\Browser;

it('invites a team member', function () {
    [$company, $user] = createStaffUser();
    $email = fake()->unique()->safeEmail();

    $this->browse(function (Browser $browser) use ($user, $email) {
        loginAsStaff($browser, $user);

        $browser->visit('/team')
            ->waitForLivewireToLoad()
            ->assertSee('Team')
            ->assertSee('Invite a team member')
            ->type('#email', $email)
            ->select('#role', 'engineer')
            ->waitForLivewire()->press('Send invitation')
            ->waitForText($email)
            ->assertSee($email);
    });
});
