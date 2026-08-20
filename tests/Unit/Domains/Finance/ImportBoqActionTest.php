<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\ImportBoqAction;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;

test('it imports boq items from parsed rows', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $rows = [
        ['item_code' => '1001', 'description' => 'Concrete slab', 'unit' => 'cu.m.', 'quantity' => '12.5', 'unit_price' => '5000', 'cost_code' => null],
        ['item_code' => '1002', 'description' => 'Rebar', 'unit' => 'kg', 'quantity' => '100', 'unit_price' => '60', 'cost_code' => null],
    ];

    $count = app(ImportBoqAction::class)->execute($rows, $project->id, $company->id);

    $this->assertSame(2, $count);

    $this->assertDatabaseHas('boq_items', [
        'company_id' => $company->id,
        'description' => 'Concrete slab',
        'total' => 6250000,
    ]);

    $this->assertDatabaseHas('boq_items', [
        'description' => 'Rebar',
        'total' => 600000,
    ]);
});
