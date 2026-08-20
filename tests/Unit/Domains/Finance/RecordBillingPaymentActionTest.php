<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\RecordBillingPaymentAction;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Carbon\CarbonImmutable;

test('recording a partial payment marks the billing partially paid', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $billing = Billing::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'net_amount' => 2_150_000,
    ]);

    $billing = app(RecordBillingPaymentAction::class)->execute(
        $billing,
        CarbonImmutable::parse('2026-08-20'),
        1_000_000,
        'bank',
    );

    $this->assertSame(BillingStatus::PartiallyPaid, $billing->status);
    $this->assertSame(1_000_000, $billing->paidAmount());
    $this->assertSame(1_150_000, $billing->balanceAmount());
});

test('recording full payment marks the billing paid', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $billing = Billing::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'net_amount' => 2_150_000,
    ]);

    $billing = app(RecordBillingPaymentAction::class)->execute(
        $billing,
        CarbonImmutable::parse('2026-08-20'),
        2_150_000,
        'bank',
    );

    $this->assertSame(BillingStatus::Paid, $billing->status);
    $this->assertSame(0, $billing->balanceAmount());
});
