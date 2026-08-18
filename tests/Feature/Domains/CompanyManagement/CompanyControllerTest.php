<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_view_their_own_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->getJson('/api/companies/'.$company->uuid)
            ->assertOk()
            ->assertJsonPath('name', $company->name);
    }

    public function test_a_user_can_list_their_own_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->getJson('/api/companies')
            ->assertOk()
            ->assertJsonPath('name', $company->name);
    }

    public function test_a_user_cannot_view_another_tenants_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);

        $this->actingAs($user)
            ->getJson('/api/companies/'.$companyB->uuid)
            ->assertForbidden();
    }

    public function test_a_user_can_update_their_own_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->putJson('/api/companies/'.$company->uuid, [
                'name' => 'Updated Name',
                'currency' => 'EUR',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Updated Name')
            ->assertJsonPath('currency', 'EUR');
    }

    public function test_a_user_cannot_update_another_tenants_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);

        $this->actingAs($user)
            ->putJson('/api/companies/'.$companyB->uuid, [
                'name' => 'Hijacked Name',
                'currency' => 'EUR',
            ])
            ->assertForbidden();
    }

    public function test_a_user_cannot_delete_another_tenants_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);

        $this->actingAs($user)
            ->deleteJson('/api/companies/'.$companyB->uuid)
            ->assertForbidden();
    }

    public function test_a_user_can_delete_their_own_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->deleteJson('/api/companies/'.$company->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }
}
