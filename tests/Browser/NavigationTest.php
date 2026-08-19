<?php

use Laravel\Dusk\Browser;

it('renders every authenticated page', function () {
    [$company, $user] = createStaffUser();

    $pages = [
        '/dashboard' => 'Dashboard',
        '/projects' => 'Projects',
        '/tasks' => 'Tasks',
        '/daily-logs' => 'Daily Logs',
        '/project-photos' => 'Site Photos',
        '/documents' => 'Documents',
        '/time-entries' => 'Time Tracking',
        '/equipment' => 'Fleet & Equipment',
        '/maintenance-logs' => 'Maintenance Logs',
        '/clients' => 'Clients',
        '/leads' => 'Leads',
        '/suppliers' => 'Suppliers',
        '/expenses' => 'Expenses',
        '/change-orders' => 'Change Orders',
        '/purchase-orders' => 'Purchase Orders',
        '/invoices' => 'Invoices',
        '/estimates' => 'Estimates',
        '/companies' => 'Company',
        '/team' => 'Team',
        '/profile' => 'Profile',
    ];

    $this->browse(function (Browser $browser) use ($user, $pages) {
        loginAsStaff($browser, $user);

        foreach ($pages as $path => $heading) {
            $browser->visit($path)
                ->assertPathIs($path)
                ->assertSee($heading);
        }
    });
});
