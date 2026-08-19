<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;

test('owner has all permissions', function () {

    $owner = User::factory()->create();

    $this->assertTrue($owner->hasRole('owner'));
    $this->assertTrue($owner->hasPermissionTo('manage_projects'));
    $this->assertTrue($owner->hasPermissionTo('manage_time_entries'));
    $this->assertTrue($owner->hasPermissionTo('manage_company'));
});

test('staff can only manage time entries', function () {

    $staff = User::factory()->role('staff')->create();

    $this->assertTrue($staff->hasRole('staff'));
    $this->assertTrue($staff->hasPermissionTo('manage_time_entries'));
    $this->assertFalse($staff->hasPermissionTo('manage_projects'));
    $this->assertFalse($staff->hasPermissionTo('manage_invoices'));
});

test('accountant manages financials but not projects', function () {

    $accountant = User::factory()->role('accountant')->create();

    $this->assertTrue($accountant->hasPermissionTo('manage_invoices'));
    $this->assertTrue($accountant->hasPermissionTo('manage_expenses'));
    $this->assertTrue($accountant->hasPermissionTo('manage_purchase_orders'));
    $this->assertFalse($accountant->hasPermissionTo('manage_projects'));
});

test('foreman manages field records but not projects', function () {

    $foreman = User::factory()->role('foreman')->create();

    $this->assertTrue($foreman->hasPermissionTo('manage_daily_logs'));
    $this->assertTrue($foreman->hasPermissionTo('manage_photos'));
    $this->assertTrue($foreman->hasPermissionTo('manage_time_entries'));
    $this->assertFalse($foreman->hasPermissionTo('manage_projects'));
});

test('project manager manages projects and sales but not company', function () {

    $pm = User::factory()->role('project_manager')->create();

    $this->assertTrue($pm->hasPermissionTo('manage_projects'));
    $this->assertTrue($pm->hasPermissionTo('manage_invoices'));
    $this->assertTrue($pm->hasPermissionTo('manage_clients'));
    $this->assertFalse($pm->hasPermissionTo('manage_company'));
});

test('client users have no staff permissions', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    $clientUser = User::factory()->client()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $this->assertTrue($clientUser->isClient());
    $this->assertFalse($clientUser->hasPermissionTo('manage_projects'));
});
