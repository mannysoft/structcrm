<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreatePurchaseOrderAction;
use App\Domains\Sales\DTOs\PurchaseOrderData;
use App\Domains\Sales\Enums\PurchaseOrderStatus;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Supplier;

test('it creates a purchase order with items and total', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);

    $purchaseOrder = app(CreatePurchaseOrderAction::class)->execute(new PurchaseOrderData(
        projectId: $project->id,
        supplierId: $supplier->id,
        status: PurchaseOrderStatus::Draft,
        items: [
            ['description' => 'Concrete', 'quantity' => 10, 'unit_price' => 10000],
            ['description' => 'Rebar', 'quantity' => 5, 'unit_price' => 20000],
        ],
    ), $company->id);

    $this->assertSame('PO-1', $purchaseOrder->po_number);
    $this->assertSame(200000, $purchaseOrder->total);
    $this->assertCount(2, $purchaseOrder->items);

    $this->assertDatabaseHas('po_items', [
        'purchase_order_id' => $purchaseOrder->id,
        'description' => 'Concrete',
    ]);
});
