<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateInvoiceAction;
use App\Domains\Sales\DTOs\InvoiceData;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Client;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateInvoiceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_invoice_with_items_and_total(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        $invoice = app(CreateInvoiceAction::class)->execute(new InvoiceData(
            projectId: $project->id,
            status: InvoiceStatus::Sent,
            issueDate: CarbonImmutable::parse('2026-08-01'),
            dueDate: CarbonImmutable::parse('2026-08-31'),
            items: [
                ['description' => 'Framing', 'quantity' => 1, 'unit_price' => 500000],
            ],
        ), $company->id);

        $this->assertSame('INV-1', $invoice->invoice_number);
        $this->assertSame(500000, $invoice->total);
        $this->assertCount(1, $invoice->items);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Framing',
        ]);
    }
}
