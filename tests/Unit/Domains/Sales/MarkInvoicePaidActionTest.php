<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\MarkInvoicePaidAction;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkInvoicePaidActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_an_invoice_as_paid(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => InvoiceStatus::Sent,
        ]);

        app(MarkInvoicePaidAction::class)->execute($invoice);

        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);
    }
}
