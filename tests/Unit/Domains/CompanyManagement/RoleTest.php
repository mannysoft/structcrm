<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Enums\Role;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_has_every_capability(): void
    {
        $this->assertTrue(Role::Owner->can('manage_projects'));
        $this->assertTrue(Role::Owner->can('manage_time_entries'));
        $this->assertTrue(Role::Owner->can('manage_company'));
    }

    public function test_staff_can_only_manage_time_entries(): void
    {
        $this->assertTrue(Role::Staff->can('manage_time_entries'));
        $this->assertFalse(Role::Staff->can('manage_projects'));
        $this->assertFalse(Role::Staff->can('manage_invoices'));
    }

    public function test_accountant_manages_financials_but_not_projects(): void
    {
        $this->assertTrue(Role::Accountant->can('manage_invoices'));
        $this->assertTrue(Role::Accountant->can('manage_expenses'));
        $this->assertTrue(Role::Accountant->can('manage_purchase_orders'));
        $this->assertTrue(Role::Accountant->can('manage_suppliers'));
        $this->assertFalse(Role::Accountant->can('manage_projects'));
    }

    public function test_foreman_manages_field_records_but_not_projects(): void
    {
        $this->assertTrue(Role::Foreman->can('manage_daily_logs'));
        $this->assertTrue(Role::Foreman->can('manage_photos'));
        $this->assertTrue(Role::Foreman->can('manage_time_entries'));
        $this->assertFalse(Role::Foreman->can('manage_projects'));
        $this->assertFalse(Role::Foreman->can('manage_invoices'));
    }

    public function test_project_manager_manages_projects_and_sales_but_not_company(): void
    {
        $this->assertTrue(Role::ProjectManager->can('manage_projects'));
        $this->assertTrue(Role::ProjectManager->can('manage_invoices'));
        $this->assertTrue(Role::ProjectManager->can('manage_clients'));
        $this->assertTrue(Role::ProjectManager->can('manage_leads'));
        $this->assertFalse(Role::ProjectManager->can('manage_company'));
    }

    public function test_client_users_have_no_staff_capabilities(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);

        $clientUser = User::factory()->client()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        $this->assertTrue($clientUser->isClient());
        $this->assertFalse($clientUser->hasCapability('manage_projects'));
    }

    public function test_staff_user_has_capability_via_role(): void
    {
        $company = Company::factory()->create();
        $staff = User::factory()->role(Role::Staff)->create(['company_id' => $company->id]);

        $this->assertTrue($staff->hasCapability('manage_time_entries'));
        $this->assertFalse($staff->hasCapability('manage_projects'));
        $this->assertTrue($staff->hasRole(Role::Staff));
    }
}
