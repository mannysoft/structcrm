<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_a_supplier(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/suppliers', [
            'name' => 'Acme Materials',
            'email' => 'sales@acme.test',
        ])->assertCreated();

        $this->assertDatabaseHas('suppliers', [
            'company_id' => $company->id,
            'name' => 'Acme Materials',
        ]);
    }

    public function test_a_user_can_list_their_suppliers(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Supplier::factory()->count(2)->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->getJson('/api/suppliers')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_update_their_supplier(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $supplier = Supplier::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->putJson('/api/suppliers/'.$supplier->uuid, [
                'name' => 'Updated Supplier',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Updated Supplier');
    }

    public function test_a_user_can_delete_their_supplier(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $supplier = Supplier::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->deleteJson('/api/suppliers/'.$supplier->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_a_user_cannot_view_another_tenants_supplier(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $supplierB = Supplier::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($user)
            ->getJson('/api/suppliers/'.$supplierB->uuid)
            ->assertNotFound();
    }
}
