<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Tools\Analytics\GetProjectProfitability;
use App\Ai\Tools\Finance\CreateBilling;
use App\Ai\Tools\Finance\CreateBoqItem;
use App\Ai\Tools\Finance\CreateBudget;
use App\Ai\Tools\Finance\CreateContract;
use App\Ai\Tools\Finance\CreateCostCode;
use App\Ai\Tools\Finance\CreateProgressReport;
use App\Ai\Tools\Finance\DeleteBilling;
use App\Ai\Tools\Finance\DeleteBoqItem;
use App\Ai\Tools\Finance\DeleteBudget;
use App\Ai\Tools\Finance\DeleteContract;
use App\Ai\Tools\Finance\DeleteCostCode;
use App\Ai\Tools\Finance\DeleteProgressReport;
use App\Ai\Tools\Finance\GetBilling;
use App\Ai\Tools\Finance\GetBudget;
use App\Ai\Tools\Finance\GetProgressReport;
use App\Ai\Tools\Finance\ListBillingPayments;
use App\Ai\Tools\Finance\RecordBillingPayment;
use App\Ai\Tools\Finance\UpdateBilling;
use App\Ai\Tools\Finance\UpdateBillingStatus;
use App\Ai\Tools\Finance\UpdateBoqItem;
use App\Ai\Tools\Finance\UpdateBudget;
use App\Ai\Tools\Finance\UpdateContract;
use App\Ai\Tools\Finance\UpdateCostCode;
use App\Ai\Tools\Finance\UpdateProgressReport;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\Budget;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Models\CostCode;
use App\Domains\Finance\Models\ProgressReport;
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

test('the assistant can update and delete cost codes, contracts and BOQ items', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $project = makeAiProject($company);
    $owner = User::factory()->create(['company_id' => $company->id]);

    $costCode = json_decode(
        (string) (new CreateCostCode($owner))->handle(new Request([
            'code' => '03-100',
            'name' => 'Concrete',
            'type' => 'material',
        ])),
        true,
    );

    $costCodeUuid = $costCode['data']['cost_code']['uuid'];

    $updatedCostCode = json_decode(
        (string) (new UpdateCostCode($owner))->handle(new Request([
            'cost_code' => $costCodeUuid,
            'name' => 'Concrete and formwork',
            'type' => 'subcontract',
        ])),
        true,
    );

    expect($updatedCostCode['ok'])->toBeTrue()
        ->and($updatedCostCode['data']['cost_code']['name'])->toBe('Concrete and formwork')
        ->and($updatedCostCode['data']['cost_code']['type'])->toBe('subcontract');

    $contract = json_decode(
        (string) (new CreateContract($owner))->handle(new Request([
            'project' => $project->uuid,
            'title' => 'Main contract',
            'contract_value' => 5000000,
            'status' => 'signed',
        ])),
        true,
    );

    $contractUuid = $contract['data']['contract']['uuid'];

    $updatedContract = json_decode(
        (string) (new UpdateContract($owner))->handle(new Request([
            'contract' => $contractUuid,
            'contract_value' => 5250000,
            'status' => 'completed',
        ])),
        true,
    );

    expect($updatedContract['ok'])->toBeTrue()
        ->and($updatedContract['data']['contract']['contract_value'])->toEqual(5250000)
        ->and($updatedContract['data']['contract']['status'])->toBe('completed');

    $boqItem = json_decode(
        (string) (new CreateBoqItem($owner))->handle(new Request([
            'project' => $project->uuid,
            'description' => 'Reinforced concrete',
            'quantity' => 100,
            'unit' => 'cu.m',
            'unit_price' => 200,
            'cost_code' => '03-100',
        ])),
        true,
    );

    $boqUuid = $boqItem['data']['item']['uuid'];

    $updatedBoqItem = json_decode(
        (string) (new UpdateBoqItem($owner))->handle(new Request([
            'boq_item' => $boqUuid,
            'quantity' => 120,
            'unit_price' => 250,
        ])),
        true,
    );

    expect($updatedBoqItem['ok'])->toBeTrue()
        ->and($updatedBoqItem['data']['item']['quantity'])->toEqual(120)
        ->and($updatedBoqItem['data']['item']['total'])->toEqual(30000);

    expect(json_decode((string) (new DeleteBoqItem($owner))->handle(new Request(['boq_item' => $boqUuid])), true)['ok'])->toBeTrue()
        ->and(json_decode((string) (new DeleteContract($owner))->handle(new Request(['contract' => $contractUuid])), true)['ok'])->toBeTrue()
        ->and(json_decode((string) (new DeleteCostCode($owner))->handle(new Request(['cost_code' => $costCodeUuid])), true)['ok'])->toBeTrue()
        ->and(BoqItem::query()->count())->toBe(0)
        ->and(Contract::query()->count())->toBe(0)
        ->and(CostCode::query()->count())->toBe(0);
});

test('the assistant can update and delete budgets, progress reports and billings', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $project = makeAiProject($company);
    $owner = User::factory()->create(['company_id' => $company->id]);

    $budget = json_decode(
        (string) (new CreateBudget($owner))->handle(new Request([
            'project' => $project->uuid,
            'items' => [
                ['description' => 'Structural works', 'quantity' => 10, 'unit_price' => 1000],
            ],
        ])),
        true,
    );

    $budgetUuid = $budget['data']['budget']['uuid'];

    $budgetDetail = json_decode((string) (new GetBudget($owner))->handle(new Request(['budget' => $budgetUuid])), true);

    expect($budgetDetail['ok'])->toBeTrue()
        ->and($budgetDetail['data']['items'])->toHaveCount(1);

    $updatedBudget = json_decode(
        (string) (new UpdateBudget($owner))->handle(new Request([
            'budget' => $budgetUuid,
            'status' => 'approved',
            'forecast' => 12000,
        ])),
        true,
    );

    expect($updatedBudget['ok'])->toBeTrue()
        ->and($updatedBudget['data']['budget']['status'])->toBe('approved')
        ->and($updatedBudget['data']['budget']['forecast'])->toEqual(12000)
        ->and($updatedBudget['data']['budget']['items_count'])->toBe(1);

    $report = json_decode(
        (string) (new CreateProgressReport($owner))->handle(new Request([
            'project' => $project->uuid,
            'report_date' => '2026-11-01',
            'items' => [
                ['description' => 'Structural works', 'quantity' => 10, 'accomplished_amount' => 500],
            ],
        ])),
        true,
    );

    $reportUuid = $report['data']['progress_report']['uuid'];

    $reportDetail = json_decode((string) (new GetProgressReport($owner))->handle(new Request(['progress_report' => $reportUuid])), true);

    expect($reportDetail['ok'])->toBeTrue()
        ->and($reportDetail['data']['items'])->toHaveCount(1);

    $updatedReport = json_decode(
        (string) (new UpdateProgressReport($owner))->handle(new Request([
            'progress_report' => $reportUuid,
            'status' => 'submitted',
            'notes' => 'Revised after site inspection.',
        ])),
        true,
    );

    expect($updatedReport['ok'])->toBeTrue()
        ->and($updatedReport['data']['progress_report']['status'])->toBe('submitted')
        ->and($updatedReport['data']['progress_report']['accomplished'])->toEqual(500);

    $billing = json_decode(
        (string) (new CreateBilling($owner))->handle(new Request([
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

    $billingUuid = $billing['data']['billing']['uuid'];

    $updatedBilling = json_decode(
        (string) (new UpdateBilling($owner))->handle(new Request([
            'billing' => $billingUuid,
            'retention_rate' => 5,
            'other_deductions' => 1000,
            'status' => 'approved',
        ])),
        true,
    );

    expect($updatedBilling['ok'])->toBeTrue()
        ->and($updatedBilling['data']['billing']['gross_amount'])->toEqual(100000)
        ->and($updatedBilling['data']['billing']['retention_amount'])->toEqual(5000)
        ->and($updatedBilling['data']['billing']['net_amount'])->toEqual(94000)
        ->and($updatedBilling['data']['billing']['status'])->toBe('approved');

    json_decode(
        (string) (new RecordBillingPayment($owner))->handle(new Request([
            'billing' => $billingUuid,
            'amount' => 94000,
        ])),
        true,
    );

    $payments = json_decode((string) (new ListBillingPayments($owner))->handle(new Request(['billing' => $billingUuid])), true);

    expect($payments['ok'])->toBeTrue()
        ->and($payments['data']['count'])->toBe(1)
        ->and($payments['data']['total'])->toEqual(94000);

    expect(json_decode((string) (new DeleteBilling($owner))->handle(new Request(['billing' => $billingUuid])), true)['ok'])->toBeTrue()
        ->and(json_decode((string) (new DeleteProgressReport($owner))->handle(new Request(['progress_report' => $reportUuid])), true)['ok'])->toBeTrue()
        ->and(json_decode((string) (new DeleteBudget($owner))->handle(new Request(['budget' => $budgetUuid])), true)['ok'])->toBeTrue()
        ->and(Billing::query()->count())->toBe(0)
        ->and(ProgressReport::query()->count())->toBe(0)
        ->and(Budget::query()->count())->toBe(0);
});
