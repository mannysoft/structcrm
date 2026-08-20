<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

function billingPayload(Project $project): array
{
    return [
        'project_id' => $project->id,
        'period_start' => '2026-08-01',
        'period_end' => '2026-08-31',
        'status' => BillingStatus::Submitted->value,
        'retention_rate' => 10,
        'other_deductions' => 1000,
        'items' => [
            ['description' => 'Foundation works', 'quantity' => 1, 'unit_price' => 2500000],
        ],
    ];
}

test('a user can create a billing', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/billings', billingPayload($project))->assertCreated();

    $this->assertDatabaseHas('billings', [
        'company_id' => $company->id,
        'project_id' => $project->id,
        'net_amount' => 2_150_000,
    ]);
});

test('a user can list their billings', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    foreach (range(1, 2) as $i) {
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        Billing::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);
    }

    $this->actingAs($user)
        ->getJson('/api/billings')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can update their billing', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $billing = Billing::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

    $this->actingAs($user)
        ->putJson('/api/billings/'.$billing->uuid, billingPayload($project))
        ->assertOk()
        ->assertJsonPath('status', BillingStatus::Submitted->value);
});

test('a user can delete their billing', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $billing = Billing::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

    $this->actingAs($user)
        ->deleteJson('/api/billings/'.$billing->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('billings', ['id' => $billing->id]);
});

test('a user can record a payment against a billing', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $billing = Billing::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'net_amount' => 2_150_000,
    ]);

    $this->actingAs($user)
        ->postJson('/api/billings/'.$billing->uuid.'/payments', [
            'payment_date' => '2026-08-20',
            'amount' => 1_000_000,
            'method' => 'bank',
        ])
        ->assertOk()
        ->assertJsonPath('paid_amount', 1_000_000.0)
        ->assertJsonPath('status', BillingStatus::PartiallyPaid->value);
});

test('a user cannot access another tenants billing', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $billingB = Billing::factory()->create(['company_id' => $companyB->id, 'project_id' => $projectB->id]);

    $this->actingAs($userA)
        ->getJson('/api/billings/'.$billingB->uuid)
        ->assertNotFound();
});
