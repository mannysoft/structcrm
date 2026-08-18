<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createInvoice(Company $company, Project $project): Invoice
    {
        return Invoice::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);
    }

    public function test_a_user_can_create_an_invoice_with_items(): void
    {
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
    }

    public function test_a_user_can_list_their_invoices(): void
    {
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
    }

    public function test_a_user_can_delete_their_invoice(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $invoice = $this->createInvoice($company, $project);

        $this->actingAs($user)
            ->deleteJson('/api/invoices/'.$invoice->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    public function test_a_user_cannot_view_another_tenants_invoice(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $invoiceB = $this->createInvoice($companyB, $projectB);

        $this->actingAs($user)
            ->getJson('/api/invoices/'.$invoiceB->uuid)
            ->assertNotFound();
    }
}
