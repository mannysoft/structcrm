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

function createPurchaseOrder(Company $company, Project $project, Supplier $supplier): PurchaseOrder
{

    return PurchaseOrder::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'supplier_id' => $supplier->id,
    ]);
}

test('a user can create a purchase order with items', function () {

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
});

test('a user can list their purchase orders', function () {

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
});

test('a user can delete their purchase order', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $purchaseOrder = createPurchaseOrder($company, $project, $supplier);

    $this->actingAs($user)
        ->deleteJson('/api/purchase-orders/'.$purchaseOrder->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('purchase_orders', ['id' => $purchaseOrder->id]);
});

test('a user cannot view another tenants purchase order', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $supplierB = Supplier::factory()->create(['company_id' => $companyB->id]);
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $purchaseOrderB = createPurchaseOrder($companyB, $projectB, $supplierB);

    $this->actingAs($user)
        ->getJson('/api/purchase-orders/'.$purchaseOrderB->uuid)
        ->assertNotFound();
});
