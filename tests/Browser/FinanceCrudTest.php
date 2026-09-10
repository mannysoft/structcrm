<?php

use Laravel\Dusk\Browser;

it('creates a cost code', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/cost-codes/create')
            ->type('#code', 'CC-001')
            ->type('#name', 'Site preparation')
            ->press('Save')
            ->waitForLocation('/cost-codes')
            ->assertPathIs('/cost-codes');
    });
});

it('creates a contract', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/contracts/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->type('#title', 'Main construction contract')
            ->type('#contract_value', '5000000')
            ->press('Save')
            ->waitForLocation('/contracts')
            ->assertPathIs('/contracts');
    });
});

it('creates a BOQ item', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/boq-items/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->type('#description', 'Concrete pouring')
            ->type('#quantity', '10')
            ->type('#unit', 'm3')
            ->type('#unit_price', '4500')
            ->press('Save')
            ->waitForLocation('/boq-items')
            ->assertPathIs('/boq-items');
    });
});

it('imports BOQ items from a CSV file', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $path = createTempCsv('boq-import.csv', [
        ['item_code', 'description', 'unit', 'quantity', 'unit_price', 'cost_code'],
        ['B-001', 'Imported concrete', 'm3', '20', '4200', ''],
    ]);

    $this->browse(function (Browser $browser) use ($user, $project, $path) {
        loginAsStaff($browser, $user);

        $browser->visit('/boq-items/import')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->attach('#file', $path)
            ->pause(500)
            ->press('Import')
            ->waitForLocation('/boq-items')
            ->assertPathIs('/boq-items');
    });
});

it('creates a budget with line items', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/budgets/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->addRepeaterItem('Add item', [
                'input[x-model="item.description"]' => 'Foundation works',
                'input[x-model="item.unit_price"]' => '150000',
            ])
            ->press('Save')
            ->waitForLocation('/budgets')
            ->assertPathIs('/budgets');
    });
});

it('creates a progress report with accomplishment items', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/progress-reports/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->addRepeaterItem('Add item', [
                'input[x-model="item.description"]' => 'Excavation',
            ])
            ->press('Save')
            ->waitForLocation('/progress-reports')
            ->assertPathIs('/progress-reports');
    });
});

it('creates a billing with line items', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/billings/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->setInputValue('#period_start', '2026-01-01')
            ->setInputValue('#period_end', '2026-01-31')
            ->addRepeaterItem('Add item', [
                'input[x-model="item.description"]' => 'Progress billing #1',
                'input[x-model="item.unit_price"]' => '200000',
            ])
            ->press('Save')
            ->waitForLocation('/billings')
            ->assertPathIs('/billings');
    });
});
