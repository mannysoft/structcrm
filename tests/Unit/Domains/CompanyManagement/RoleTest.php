<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_has_all_permissions(): void
    {
        $owner = User::factory()->create();

        $this->assertTrue($owner->hasRole('owner'));
        $this->assertTrue($owner->hasPermissionTo('manage_projects'));
        $this->assertTrue($owner->hasPermissionTo('manage_time_entries'));
        $this->assertTrue($owner->hasPermissionTo('manage_company'));
    }

    public function test_staff_can_only_manage_time_entries(): void
    {
        $staff = User::factory()->role('staff')->create();

        $this->assertTrue($staff->hasRole('staff'));
        $this->assertTrue($staff->hasPermissionTo('manage_time_entries'));
        $this->assertFalse($staff->hasPermissionTo('manage_projects'));
        $this->assertFalse($staff->hasPermissionTo('manage_invoices'));
    }

    public function test_accountant_manages_financials_but_not_projects(): void
    {
        $accountant = User::factory()->role('accountant')->create();

        $this->assertTrue($accountant->hasPermissionTo('manage_invoices'));
        $this->assertTrue($accountant->hasPermissionTo('manage_expenses'));
        $this->assertTrue($accountant->hasPermissionTo('manage_purchase_orders'));
        $this->assertFalse($accountant->hasPermissionTo('manage_projects'));
    }

    public function test_foreman_manages_field_records_but_not_projects(): void
    {
        $foreman = User::factory()->role('foreman')->create();

        $this->assertTrue($foreman->hasPermissionTo('manage_daily_logs'));
        $this->assertTrue($foreman->hasPermissionTo('manage_photos'));
        $this->assertTrue($foreman->hasPermissionTo('manage_time_entries'));
        $this->assertFalse($foreman->hasPermissionTo('manage_projects'));
    }

    public function test_project_manager_manages_projects_and_sales_but_not_company(): void
    {
        $pm = User::factory()->role('project_manager')->create();

        $this->assertTrue($pm->hasPermissionTo('manage_projects'));
        $this->assertTrue($pm->hasPermissionTo('manage_invoices'));
        $this->assertTrue($pm->hasPermissionTo('manage_clients'));
        $this->assertFalse($pm->hasPermissionTo('manage_company'));
    }

    public function test_client_users_have_no_staff_permissions(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);

        $clientUser = User::factory()->client()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        $this->assertTrue($clientUser->isClient());
        $this->assertFalse($clientUser->hasPermissionTo('manage_projects'));
    }
}
