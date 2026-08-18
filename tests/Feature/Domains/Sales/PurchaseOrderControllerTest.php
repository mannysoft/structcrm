<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\PurchaseOrderStatus;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createPurchaseOrder(Company $company, Project $project, Supplier $supplier): PurchaseOrder
    {
        return PurchaseOrder::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    public function test_a_user_can_create_a_purchase_order_with_items(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $supplier = Supplier::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/purchase-orders', [
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::Draft->value,
            'items' => [
                ['description' => 'Concrete', 'quantity' => 2, 'unit_price' => 100.00],
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('purchase_orders', [
            'company_id' => $company->id,
            'total' => 20000,
        ]);
    }

    public function test_a_user_can_list_their_purchase_orders(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $supplier = Supplier::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        PurchaseOrder::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/purchase-orders')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_delete_their_purchase_order(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $supplier = Supplier::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $purchaseOrder = $this->createPurchaseOrder($company, $project, $supplier);

        $this->actingAs($user)
            ->deleteJson('/api/purchase-orders/'.$purchaseOrder->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('purchase_orders', ['id' => $purchaseOrder->id]);
    }

    public function test_a_user_cannot_view_another_tenants_purchase_order(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
        $supplierB = Supplier::factory()->create(['company_id' => $companyB->id]);
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $purchaseOrderB = $this->createPurchaseOrder($companyB, $projectB, $supplierB);

        $this->actingAs($user)
            ->getJson('/api/purchase-orders/'.$purchaseOrderB->uuid)
            ->assertNotFound();
    }
}
