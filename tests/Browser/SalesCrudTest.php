<?php

use App\Domains\Sales\Models\Supplier;
use Laravel\Dusk\Browser;

it('creates a lead', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/leads/create')
            ->type('#first_name', 'Maria')
            ->type('#last_name', 'Santos')
            ->press('Save')
            ->waitForLocation('/leads')
            ->assertPathIs('/leads');
    });
});

it('creates a supplier', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/suppliers/create')
            ->type('#name', 'BuildMart Supply')
            ->press('Save')
            ->waitForLocation('/suppliers')
            ->assertPathIs('/suppliers');
    });
});

it('creates an expense', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/expenses/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->type('#amount', '12500')
            ->setInputValue('#date', '2026-01-15')
            ->press('Save')
            ->waitForLocation('/expenses')
            ->assertPathIs('/expenses');
    });
});

it('creates a change order without items', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/change-orders/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->type('#description', 'Additional structural steel')
            ->type('#amount', '85000')
            ->press('Save')
            ->waitForLocation('/change-orders')
            ->assertPathIs('/change-orders');
    });
});

it('creates an invoice with line items', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/invoices/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->setInputValue('#issue_date', '2026-01-01')
            ->setInputValue('#due_date', '2026-02-01')
            ->addRepeaterItem('Add item', [
                'input[x-model="item.description"]' => 'General works',
                'input[x-model="item.unit_price"]' => '5000',
            ])
            ->press('Save')
            ->waitForLocation('/invoices')
            ->assertPathIs('/invoices');
    });
});

it('creates an estimate with line items', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/estimates/create')
            ->type('#title', 'Warehouse fit-out')
            ->addRepeaterItem('Add item', [
                'input[x-model="item.description"]' => 'Painting',
                'input[x-model="item.unit_price"]' => '3000',
            ])
            ->press('Save')
            ->waitForLocation('/estimates')
            ->assertPathIs('/estimates');
    });
});

it('creates a purchase order with line items', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);

    $this->browse(function (Browser $browser) use ($user, $project, $supplier) {
        loginAsStaff($browser, $user);

        $browser->visit('/purchase-orders/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->select('#supplier_id', (string) $supplier->id)
            ->pause(500)
            ->addRepeaterItem('Add item', [
                'input[x-model="item.description"]' => 'Rebar',
                'input[x-model="item.unit_price"]' => '2000',
            ])
            ->press('Save')
            ->waitForLocation('/purchase-orders')
            ->assertPathIs('/purchase-orders');
    });
});
