<?php

use App\Domains\ProjectManagement\Models\Equipment;
use Laravel\Dusk\Browser;

it('creates a task', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/tasks/create')
            ->type('#title', 'Pour foundation')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->press('Save')
            ->waitForLocation('/tasks')
            ->assertPathIs('/tasks');
    });
});

it('creates a daily log', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/daily-logs/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->setInputValue('#log_date', '2026-01-15')
            ->type('#work_performed', 'Poured foundation slab')
            ->press('Save')
            ->waitForLocation('/daily-logs')
            ->assertPathIs('/daily-logs');
    });
});

it('clocks in a time entry', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->browse(function (Browser $browser) use ($user, $project) {
        loginAsStaff($browser, $user);

        $browser->visit('/time-entries/create')
            ->select('#project_id', (string) $project->id)
            ->pause(500)
            ->setInputValue('#clock_in_at', '2026-01-15T08:00')
            ->pause(500)
            ->press('Clock in')
            ->waitForLocation('/time-entries')
            ->assertPathIs('/time-entries');
    });
});

it('adds equipment', function () {
    [$company, $user] = createStaffUser();

    $this->browse(function (Browser $browser) use ($user) {
        loginAsStaff($browser, $user);

        $browser->visit('/equipment/create')
            ->type('#name', 'Excavator X200')
            ->press('Save')
            ->waitForLocation('/equipment')
            ->assertPathIs('/equipment');
    });
});

it('logs equipment maintenance', function () {
    [$company, $user] = createStaffUser();
    $equipment = Equipment::factory()->create(['company_id' => $company->id]);

    $this->browse(function (Browser $browser) use ($user, $equipment) {
        loginAsStaff($browser, $user);

        $browser->visit('/maintenance-logs/create')
            ->select('#equipment_id', (string) $equipment->id)
            ->pause(500)
            ->setInputValue('#service_date', '2026-01-20')
            ->type('#description', 'Oil change and inspection')
            ->press('Save')
            ->waitForLocation('/maintenance-logs')
            ->assertPathIs('/maintenance-logs');
    });
});

it('uploads a document', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $path = createTempFile('blueprint.txt', 'Blueprint contents');

    $this->browse(function (Browser $browser) use ($user, $project, $path) {
        loginAsStaff($browser, $user);

        $browser->visit('/documents/create')
            ->select('#project_id', (string) $project->id)
            ->type('#name', 'Site blueprint')
            ->attach('#file', $path)
            ->pause(500)
            ->press('Upload')
            ->waitForLocation('/documents')
            ->assertPathIs('/documents');
    });
});

it('uploads a project photo', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $path = createTempImage('photo.png');

    $this->browse(function (Browser $browser) use ($user, $project, $path) {
        loginAsStaff($browser, $user);

        $browser->visit('/project-photos/create')
            ->select('#project_id', (string) $project->id)
            ->type('#caption', 'Foundation progress')
            ->attach('#photo', $path)
            ->waitFor('img[alt="Preview"]')
            ->press('Upload')
            ->waitForLocation('/project-photos')
            ->assertPathIs('/project-photos');
    });
});
