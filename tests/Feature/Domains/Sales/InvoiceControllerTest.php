<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use App\Models\User;

function createInvoice(Company $company, Project $project): Invoice
{

    return Invoice::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);
}

test('a user can create an invoice with items', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/invoices', [
        'project_id' => $project->id,
        'status' => InvoiceStatus::Draft->value,
        'issue_date' => '2026-08-01',
        'due_date' => '2026-08-31',
        'items' => [
            ['description' => 'Framing', 'quantity' => 1, 'unit_price' => 500.00],
        ],
    ])->assertCreated();

    $this->assertDatabaseHas('invoices', [
        'company_id' => $company->id,
        'total' => 50000,
    ]);
});

test('a user can list their invoices', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    Invoice::factory()->count(2)->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/invoices')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can delete their invoice', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $invoice = createInvoice($company, $project);

    $this->actingAs($user)
        ->deleteJson('/api/invoices/'.$invoice->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
});

test('a user cannot view another tenants invoice', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $invoiceB = createInvoice($companyB, $projectB);

    $this->actingAs($user)
        ->getJson('/api/invoices/'.$invoiceB->uuid)
        ->assertNotFound();
});
