<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\Billing;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;

function makeBilling(array $attributes = []): Billing
{
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);

    return Billing::factory()->create(array_merge([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'net_amount' => 2_000_000,
    ], $attributes));
}

test('fully paid billing is collected', function () {

    $billing = makeBilling(['due_date' => now()->subDays(5)->format('Y-m-d')]);
    $billing->payments()->create(['payment_date' => now(), 'amount' => 2_000_000, 'method' => 'bank']);

    $this->assertSame('paid', $billing->collectionStatus());
});

test('billing past its due date is overdue', function () {

    $billing = makeBilling(['due_date' => now()->subDays(5)->format('Y-m-d')]);

    $this->assertSame('overdue', $billing->collectionStatus());
});

test('partially paid billing is partially collected', function () {

    $billing = makeBilling(['due_date' => now()->addDays(5)->format('Y-m-d')]);
    $billing->payments()->create(['payment_date' => now(), 'amount' => 500_000, 'method' => 'bank']);

    $this->assertSame('partially_paid', $billing->collectionStatus());
});

test('unpaid billing with a future due date is outstanding', function () {

    $billing = makeBilling(['due_date' => now()->addDays(5)->format('Y-m-d')]);

    $this->assertSame('outstanding', $billing->collectionStatus());
});
