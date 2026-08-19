<?php

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;

it('redirects unauthenticated portal visits to the portal login', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/portal')
            ->waitForLocation('/portal/login')
            ->assertPathIs('/portal/login');
    });
});

it('lets a client sign in and manage their projects', function () {
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    $portalUser = User::factory()->client()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
    ]);

    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'status' => ProjectStatus::Active,
    ]);

    ChangeOrder::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'status' => ChangeOrderStatus::Pending,
    ]);

    Invoice::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'status' => InvoiceStatus::Sent,
    ]);

    $this->browse(function (Browser $browser) use ($portalUser, $project) {
        $browser->visit('/portal/login');
        $browser->driver->manage()->deleteAllCookies();
        $browser->visit('/portal/login')
            ->assertSee('Client Portal')
            ->type('#email', $portalUser->email)
            ->type('#password', 'password')
            ->press('Sign in')
            ->waitForLocation('/portal')
            ->assertPathIs('/portal')
            ->assertSee('Active Projects')
            ->assertSee($project->title);

        $browser->press('Approve')
            ->waitForText('No pending change orders.');

        // `wire:confirm` opens a native confirm() dialog; accept it
        // automatically so the mark-as-paid action proceeds.
        $browser->script('window.confirm = function () { return true; };');

        $browser->press('Mark as paid')
            ->waitForText('No open invoices.');
    });
});
