<?php

use App\Domains\Sales\Models\Client;
use Laravel\Dusk\Browser;

it('creates a project', function () {
    [$company, $user] = createStaffUser();
    $client = Client::factory()->create(['company_id' => $company->id]);

    $this->browse(function (Browser $browser) use ($user, $client) {
        loginAsStaff($browser, $user);

        $browser->visit('/projects/create')
            ->assertSee('Title')
            ->type('#title', 'Riverside Office Build')
            ->select('#client_id', (string) $client->id)
            ->type('#budget', '250000')
            ->press('Save')
            ->waitForLocation('/projects')
            ->assertPathIs('/projects')
            ->assertSee('Riverside Office Build');
    });
});
