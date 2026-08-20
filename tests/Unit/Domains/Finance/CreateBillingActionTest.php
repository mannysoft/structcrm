<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\CreateBillingAction;
use App\Domains\Finance\DTOs\BillingData;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Carbon\CarbonImmutable;

test('it creates a billing with retention and deductions', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $billing = app(CreateBillingAction::class)->execute(new BillingData(
        projectId: $project->id,
        progressReportId: null,
        periodStart: CarbonImmutable::parse('2026-08-01'),
        periodEnd: CarbonImmutable::parse('2026-08-31'),
        status: BillingStatus::Submitted,
        retentionRate: 0.10,
        otherDeductions: 100_000,
        currency: 'PHP',
        items: [
            ['description' => 'Foundation works', 'quantity' => 1, 'unit_price' => 2_500_000],
        ],
    ), $company->id);

    $this->assertSame('BIL-1', $billing->billing_number);
    $this->assertSame(2_500_000, $billing->gross_amount);
    $this->assertSame(250_000, $billing->retention_amount);
    $this->assertSame(2_150_000, $billing->net_amount);
    $this->assertCount(1, $billing->items);

    $this->assertDatabaseHas('billings', [
        'id' => $billing->id,
        'net_amount' => 2_150_000,
    ]);
});
