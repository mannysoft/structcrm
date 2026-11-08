<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Tools\Sales\CreateChangeOrder;
use App\Ai\Tools\Sales\CreateClient;
use App\Ai\Tools\Sales\CreateEstimate;
use App\Ai\Tools\Sales\CreateExpense;
use App\Ai\Tools\Sales\CreateInvoice;
use App\Ai\Tools\Sales\CreateLead;
use App\Ai\Tools\Sales\CreatePurchaseOrder;
use App\Ai\Tools\Sales\CreateSupplier;
use App\Ai\Tools\Sales\DeleteChangeOrder;
use App\Ai\Tools\Sales\DeleteEstimate;
use App\Ai\Tools\Sales\DeleteInvoice;
use App\Ai\Tools\Sales\DeletePurchaseOrder;
use App\Ai\Tools\Sales\GetChangeOrder;
use App\Ai\Tools\Sales\GetEstimate;
use App\Ai\Tools\Sales\GetInvoice;
use App\Ai\Tools\Sales\GetPurchaseOrder;
use App\Ai\Tools\Sales\ListClients;
use App\Ai\Tools\Sales\UpdateChangeOrder;
use App\Ai\Tools\Sales\UpdateEstimate;
use App\Ai\Tools\Sales\UpdateExpense;
use App\Ai\Tools\Sales\UpdateInvoice;
use App\Ai\Tools\Sales\UpdateInvoiceStatus;
use App\Ai\Tools\Sales\UpdateLead;
use App\Ai\Tools\Sales\UpdatePurchaseOrder;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Estimate;
use App\Domains\Sales\Models\Invoice;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Models\User;
use Laravel\Ai\Tools\Request;

test('the assistant can create and list clients', function () {
    $company = Company::factory()->create();
    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $created = json_decode(
        (string) (new CreateClient($user))->handle(new Request([
            'name' => 'Jane Doe',
            'company_name' => 'Doe Holdings',
            'email' => 'jane@example.test',
        ])),
        true,
    );

    expect($created['ok'])->toBeTrue()
        ->and($created['data']['client']['name'])->toBe('Jane Doe');

    $listed = json_decode(
        (string) (new ListClients($user))->handle(new Request(['search' => 'Doe'])),
        true,
    );

    expect($listed['data']['clients'])->toHaveCount(1)
        ->and($listed['data']['clients'][0]['company_name'])->toBe('Doe Holdings');
});

test('the assistant can move a lead through the pipeline', function () {
    $company = Company::factory()->create();
    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $created = json_decode(
        (string) (new CreateLead($user))->handle(new Request([
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
            'email' => 'ana@example.test',
        ])),
        true,
    );

    expect($created['ok'])->toBeTrue()
        ->and($created['data']['lead']['status'])->toBe('new');

    $updated = json_decode(
        (string) (new UpdateLead($user))->handle(new Request([
            'lead' => $created['data']['lead']['uuid'],
            'status' => 'qualified',
        ])),
        true,
    );

    expect($updated['ok'])->toBeTrue()
        ->and($updated['data']['lead']['status'])->toBe('qualified');
});

test('the assistant can create an invoice and mark it paid', function () {
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $accountant = User::factory()->role('accountant')->create(['company_id' => $company->id]);

    $created = json_decode(
        (string) (new CreateInvoice($accountant))->handle(new Request([
            'project' => $project->uuid,
            'status' => 'sent',
            'items' => [
                ['description' => 'Concrete works', 'quantity' => 2, 'unit_price' => 1500],
            ],
        ])),
        true,
    );

    expect($created['ok'])->toBeTrue();

    $invoice = Invoice::query()->where('uuid', $created['data']['invoice']['uuid'])->firstOrFail();

    expect($invoice->total)->toBe(300_000);

    $updated = json_decode(
        (string) (new UpdateInvoiceStatus($accountant))->handle(new Request([
            'invoice' => $invoice->invoice_number,
            'status' => 'paid',
        ])),
        true,
    );

    expect($updated['ok'])->toBeTrue()
        ->and($updated['data']['invoice']['status'])->toBe('paid');
});

test('sales tools reject records belonging to another company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $foreignClient = Client::factory()->create(['company_id' => $otherCompany->id]);

    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $result = json_decode(
        (string) (new CreateInvoice($user))->handle(new Request([
            'project' => $foreignClient->uuid,
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ])),
        true,
    );

    expect($result['ok'])->toBeFalse();
});

test('the assistant can read, update and delete estimates and invoices', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $client = Client::factory()->create(['company_id' => $company->id]);
    $owner = User::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $estimate = json_decode(
        (string) (new CreateEstimate($owner))->handle(new Request([
            'title' => 'Perimeter fence',
            'project' => $project->uuid,
            'items' => [
                ['description' => 'Fence panels', 'quantity' => 10, 'unit_price' => 100],
            ],
        ])),
        true,
    );

    $estimateUuid = $estimate['data']['estimate']['uuid'];

    $detail = json_decode((string) (new GetEstimate($owner))->handle(new Request(['estimate' => $estimateUuid])), true);

    expect($detail['ok'])->toBeTrue()
        ->and($detail['data']['items'])->toHaveCount(1)
        ->and($detail['data']['items'][0]['unit_price'])->toEqual(100);

    $updated = json_decode(
        (string) (new UpdateEstimate($owner))->handle(new Request([
            'estimate' => $estimateUuid,
            'tax_rate' => 10,
            'status' => 'sent',
        ])),
        true,
    );

    expect($updated['ok'])->toBeTrue()
        ->and($updated['data']['estimate']['amount'])->toEqual(1100)
        ->and($updated['data']['estimate']['status'])->toBe('sent')
        ->and($updated['data']['estimate']['items_count'])->toBe(1);

    $deleted = json_decode((string) (new DeleteEstimate($owner))->handle(new Request(['estimate' => $estimateUuid])), true);

    expect($deleted['ok'])->toBeTrue()
        ->and(Estimate::query()->count())->toBe(0);

    $invoice = json_decode(
        (string) (new CreateInvoice($owner))->handle(new Request([
            'project' => $project->uuid,
            'items' => [
                ['description' => 'Fence installation', 'quantity' => 1, 'unit_price' => 800],
            ],
        ])),
        true,
    );

    $invoiceUuid = $invoice['data']['invoice']['uuid'];

    $invoiceDetail = json_decode((string) (new GetInvoice($owner))->handle(new Request(['invoice' => $invoiceUuid])), true);

    expect($invoiceDetail['ok'])->toBeTrue()
        ->and($invoiceDetail['data']['items'])->toHaveCount(1);

    $updatedInvoice = json_decode(
        (string) (new UpdateInvoice($owner))->handle(new Request([
            'invoice' => $invoiceUuid,
            'status' => 'sent',
            'items' => [
                ['description' => 'Fence panels', 'quantity' => 10, 'unit_price' => 100],
                ['description' => 'Gate', 'quantity' => 1, 'unit_price' => 250],
            ],
        ])),
        true,
    );

    expect($updatedInvoice['ok'])->toBeTrue()
        ->and($updatedInvoice['data']['invoice']['total'])->toEqual(1250)
        ->and($updatedInvoice['data']['invoice']['status'])->toBe('sent')
        ->and($updatedInvoice['data']['invoice']['items_count'])->toBe(2);

    $deletedInvoice = json_decode((string) (new DeleteInvoice($owner))->handle(new Request(['invoice' => $invoiceUuid])), true);

    expect($deletedInvoice['ok'])->toBeTrue()
        ->and(Invoice::query()->count())->toBe(0);
});

test('the assistant can read, update and delete change orders, purchase orders and expenses', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $client = Client::factory()->create(['company_id' => $company->id]);
    $owner = User::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $changeOrder = json_decode(
        (string) (new CreateChangeOrder($owner))->handle(new Request([
            'project' => $project->uuid,
            'description' => 'Additional drainage',
            'amount' => 1500,
        ])),
        true,
    );

    $changeOrderUuid = $changeOrder['data']['change_order']['uuid'];

    $changeOrderDetail = json_decode((string) (new GetChangeOrder($owner))->handle(new Request(['change_order' => $changeOrderUuid])), true);

    expect($changeOrderDetail['ok'])->toBeTrue()
        ->and($changeOrderDetail['data']['change_order']['status'])->toBe('pending');

    $updatedChangeOrder = json_decode(
        (string) (new UpdateChangeOrder($owner))->handle(new Request([
            'change_order' => $changeOrderUuid,
            'amount' => 2200,
            'items' => [
                ['category' => 'material', 'description' => 'Drainage pipes', 'amount' => 1700],
                ['category' => 'labor', 'description' => 'Excavation', 'amount' => 500],
            ],
        ])),
        true,
    );

    expect($updatedChangeOrder['ok'])->toBeTrue()
        ->and($updatedChangeOrder['data']['change_order']['amount'])->toEqual(2200)
        ->and($updatedChangeOrder['data']['change_order']['status'])->toBe('pending');

    $supplier = json_decode(
        (string) (new CreateSupplier($owner))->handle(new Request([
            'name' => 'Builders Depot',
            'email' => 'sales@depot.test',
        ])),
        true,
    );

    $order = json_decode(
        (string) (new CreatePurchaseOrder($owner))->handle(new Request([
            'project' => $project->uuid,
            'supplier' => $supplier['data']['supplier']['uuid'],
            'items' => [
                ['description' => 'Cement bags', 'quantity' => 20, 'unit_price' => 25],
            ],
        ])),
        true,
    );

    $orderUuid = $order['data']['purchase_order']['uuid'];

    $orderDetail = json_decode((string) (new GetPurchaseOrder($owner))->handle(new Request(['purchase_order' => $orderUuid])), true);

    expect($orderDetail['ok'])->toBeTrue()
        ->and($orderDetail['data']['items'])->toHaveCount(1);

    $updatedOrder = json_decode(
        (string) (new UpdatePurchaseOrder($owner))->handle(new Request([
            'purchase_order' => $orderUuid,
            'status' => 'approved',
        ])),
        true,
    );

    expect($updatedOrder['ok'])->toBeTrue()
        ->and($updatedOrder['data']['purchase_order']['status'])->toBe('approved')
        ->and($updatedOrder['data']['purchase_order']['items_count'])->toBe(1);

    $expense = json_decode(
        (string) (new CreateExpense($owner))->handle(new Request([
            'project' => $project->uuid,
            'category' => 'fuel',
            'amount' => 320,
        ])),
        true,
    );

    $updatedExpense = json_decode(
        (string) (new UpdateExpense($owner))->handle(new Request([
            'expense' => $expense['data']['expense']['uuid'],
            'amount' => 400,
            'category' => 'materials',
            'notes' => 'Corrected receipt total.',
        ])),
        true,
    );

    expect($updatedExpense['ok'])->toBeTrue()
        ->and($updatedExpense['data']['expense']['amount'])->toEqual(400)
        ->and($updatedExpense['data']['expense']['category'])->toBe('materials');

    expect(json_decode((string) (new DeleteChangeOrder($owner))->handle(new Request(['change_order' => $changeOrderUuid])), true)['ok'])->toBeTrue()
        ->and(json_decode((string) (new DeletePurchaseOrder($owner))->handle(new Request(['purchase_order' => $orderUuid])), true)['ok'])->toBeTrue()
        ->and(ChangeOrder::query()->count())->toBe(0)
        ->and(PurchaseOrder::query()->count())->toBe(0);
});
