<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Tools\Analytics\GetProjectProfitability;
use App\Ai\Tools\Finance\CreateBilling;
use App\Ai\Tools\Finance\GetBilling;
use App\Ai\Tools\Finance\RecordBillingPayment;
use App\Ai\Tools\Finance\UpdateBillingStatus;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\Billing;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Laravel\Ai\Tools\Request;

function makeAiProject(Company $company): Project
{
    $client = Client::factory()->create(['company_id' => $company->id]);

    return Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
}

test('the assistant can create a billing and record a payment', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $project = makeAiProject($company);
    $accountant = User::factory()->role('accountant')->create(['company_id' => $company->id]);

    $created = json_decode(
        (string) (new CreateBilling($accountant))->handle(new Request([
            'project' => $project->uuid,
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
            'retention_rate' => 10,
            'items' => [
                ['description' => 'Structural works', 'quantity' => 2, 'unit_price' => 50000],
            ],
        ])),
        true,
    );

    expect($created['ok'])->toBeTrue()
        ->and($created['data']['billing']['gross_amount'])->toEqual(100000)
        ->and($created['data']['billing']['retention_amount'])->toEqual(10000)
        ->and($created['data']['billing']['net_amount'])->toEqual(90000);

    $billing = Billing::query()->where('uuid', $created['data']['billing']['uuid'])->firstOrFail();

    $payment = json_decode(
        (string) (new RecordBillingPayment($accountant))->handle(new Request([
            'billing' => $billing->billing_number,
            'amount' => 90000,
            'method' => 'bank_transfer',
            'reference' => 'REF-001',
        ])),
        true,
    );

    expect($payment['ok'])->toBeTrue()
        ->and($payment['data']['billing']['status'])->toBe('paid')
        ->and($payment['data']['billing']['balance'])->toEqual(0);

    $detail = json_decode(
        (string) (new GetBilling($accountant))->handle(new Request(['billing' => $billing->uuid])),
        true,
    );

    expect($detail['ok'])->toBeTrue()
        ->and($detail['data']['payments'])->toHaveCount(1)
        ->and($detail['data']['payments'][0]['reference'])->toBe('REF-001');
});

test('profitability tool returns contract, budget and cost figures', function () {
    $company = Company::factory()->create();
    $project = makeAiProject($company);
    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $result = json_decode(
        (string) (new GetProjectProfitability($user))->handle(new Request(['project' => $project->uuid])),
        true,
    );

    expect($result['ok'])->toBeTrue()
        ->and($result['data'])->toHaveKeys([
            'revised_contract_value',
            'revised_budget',
            'actual_cost',
            'projected_profit',
            'projected_margin_percent',
            'cost_variance',
        ]);
});

test('billing status transitions are reflected in the assistant output', function () {
    $company = Company::factory()->create();
    $project = makeAiProject($company);
    $accountant = User::factory()->role('accountant')->create(['company_id' => $company->id]);

    $billing = Billing::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'status' => 'draft',
    ]);

    $updated = json_decode(
        (string) (new UpdateBillingStatus($accountant))->handle(new Request([
            'billing' => $billing->uuid,
            'status' => 'certified',
        ])),
        true,
    );

    expect($updated['ok'])->toBeTrue()
        ->and($updated['data']['billing']['status'])->toBe('certified');

    expect($billing->fresh()->certified_at)->not->toBeNull();
});
