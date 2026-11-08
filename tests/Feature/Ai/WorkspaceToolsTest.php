<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Tools\Finance\CreateBoqItem;
use App\Ai\Tools\Finance\CreateBudget;
use App\Ai\Tools\Finance\CreateContract;
use App\Ai\Tools\Finance\CreateCostCode;
use App\Ai\Tools\Finance\CreateProgressReport;
use App\Ai\Tools\Marketing\CreateMarketingCampaign;
use App\Ai\Tools\Projects\CreateEquipment;
use App\Ai\Tools\Projects\CreateMaintenanceLog;
use App\Ai\Tools\Sales\CreateChangeOrder;
use App\Ai\Tools\Sales\CreatePurchaseOrder;
use App\Ai\Tools\Sales\CreateSupplier;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;
use Laravel\Ai\Tools\Request;

test('the assistant can build out the commercial side of a project', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $costCode = json_decode(
        (string) (new CreateCostCode($user))->handle(new Request([
            'code' => '03-100',
            'name' => 'Concrete',
            'type' => 'material',
        ])),
        true,
    );

    expect($costCode['ok'])->toBeTrue();

    $contract = json_decode(
        (string) (new CreateContract($user))->handle(new Request([
            'project' => $project->uuid,
            'title' => 'Main contract',
            'contract_value' => 5000000,
            'status' => 'signed',
            'signed_at' => '2026-10-01',
        ])),
        true,
    );

    expect($contract['ok'])->toBeTrue()
        ->and($contract['data']['contract']['contract_value'])->toEqual(5000000);

    $boq = json_decode(
        (string) (new CreateBoqItem($user))->handle(new Request([
            'project' => $project->uuid,
            'description' => 'Concrete slab',
            'quantity' => 25,
            'unit' => 'cu.m',
            'unit_price' => 4500,
            'cost_code' => '03-100',
        ])),
        true,
    );

    expect($boq['ok'])->toBeTrue()
        ->and($boq['data']['item']['total'])->toEqual(112500);

    $budget = json_decode(
        (string) (new CreateBudget($user))->handle(new Request([
            'project' => $project->uuid,
            'type' => 'original',
            'status' => 'approved',
            'items' => [
                ['description' => 'Concrete', 'quantity' => 25, 'unit_price' => 4500, 'unit' => 'cu.m', 'cost_code' => '03-100'],
            ],
        ])),
        true,
    );

    expect($budget['ok'])->toBeTrue()
        ->and($budget['data']['budget']['is_current'])->toBeTrue()
        ->and($budget['data']['budget']['total'])->toEqual(112500);

    $report = json_decode(
        (string) (new CreateProgressReport($user))->handle(new Request([
            'project' => $project->uuid,
            'report_date' => '2026-11-07',
            'items' => [
                ['description' => 'Slab poured', 'quantity' => 10, 'accomplished_amount' => 45000, 'percent_complete' => 40],
            ],
        ])),
        true,
    );

    expect($report['ok'])->toBeTrue()
        ->and($report['data']['progress_report']['accomplished'])->toEqual(45000);
});

test('the assistant can raise purchasing documents', function () {
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $supplier = json_decode(
        (string) (new CreateSupplier($user))->handle(new Request([
            'name' => 'Steel Supply Co',
            'email' => 'sales@steel.test',
        ])),
        true,
    );

    expect($supplier['ok'])->toBeTrue();

    $order = json_decode(
        (string) (new CreatePurchaseOrder($user))->handle(new Request([
            'project' => $project->uuid,
            'supplier' => 'Steel Supply Co',
            'items' => [
                ['description' => 'Rebar 12mm', 'quantity' => 100, 'unit_price' => 250],
            ],
        ])),
        true,
    );

    expect($order['ok'])->toBeTrue()
        ->and($order['data']['purchase_order']['total'])->toEqual(25000)
        ->and(PurchaseOrder::query()->count())->toBe(1)
        ->and(Supplier::query()->count())->toBe(1);

    $changeOrder = json_decode(
        (string) (new CreateChangeOrder($user))->handle(new Request([
            'project' => $project->uuid,
            'description' => 'Additional foundation works',
            'amount' => 125000,
            'items' => [
                ['category' => 'material', 'description' => 'Extra cement', 'amount' => 125000],
            ],
        ])),
        true,
    );

    expect($changeOrder['ok'])->toBeTrue()
        ->and($changeOrder['data']['change_order']['amount'])->toEqual(125000)
        ->and($changeOrder['data']['change_order']['status'])->toBe('pending');
});

test('the assistant can manage equipment and marketing campaigns', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $equipment = json_decode(
        (string) (new CreateEquipment($user))->handle(new Request([
            'name' => 'Excavator 01',
            'type' => 'heavy_machinery',
            'status' => 'in_use',
        ])),
        true,
    );

    expect($equipment['ok'])->toBeTrue()
        ->and($equipment['data']['equipment']['type'])->toBe('heavy_machinery');

    $log = json_decode(
        (string) (new CreateMaintenanceLog($user))->handle(new Request([
            'equipment' => 'Excavator 01',
            'service_date' => '2026-11-06',
            'description' => 'Hydraulic hose replacement',
            'cost' => 8500,
        ])),
        true,
    );

    expect($log['ok'])->toBeTrue()
        ->and($log['data']['log']['cost'])->toEqual(8500)
        ->and(Equipment::query()->count())->toBe(1)
        ->and(MaintenanceLog::query()->count())->toBe(1);

    $campaign = json_decode(
        (string) (new CreateMarketingCampaign($user))->handle(new Request([
            'name' => 'November outreach',
            'is_active' => true,
            'steps' => [
                ['subject' => 'Hello', 'body' => 'Welcome!', 'delay_days' => 0],
                ['subject' => 'Follow up', 'body' => 'Any questions?', 'delay_days' => 3],
            ],
        ])),
        true,
    );

    expect($campaign['ok'])->toBeTrue()
        ->and($campaign['data']['campaign']['steps'])->toHaveCount(2)
        ->and(MarketingCampaign::query()->count())->toBe(1);
});
