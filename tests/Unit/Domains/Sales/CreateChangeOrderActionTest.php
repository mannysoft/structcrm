<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateChangeOrderAction;
use App\Domains\Sales\DTOs\ChangeOrderData;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\Client;

test('it creates a change order with a number', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $changeOrder = app(CreateChangeOrderAction::class)->execute(new ChangeOrderData(
        projectId: $project->id,
        description: 'Extra concrete work',
        amountInCents: 250000,
        status: ChangeOrderStatus::Pending,
    ), $company->id);

    $this->assertSame('Extra concrete work', $changeOrder->description);
    $this->assertSame(250000, $changeOrder->amount);
    $this->assertSame(ChangeOrderStatus::Pending, $changeOrder->status);
    $this->assertSame('CO-1', $changeOrder->co_number);

    $this->assertDatabaseHas('change_orders', [
        'id' => $changeOrder->id,
        'co_number' => 'CO-1',
    ]);
});

test('it sets approved at when created approved', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $changeOrder = app(CreateChangeOrderAction::class)->execute(new ChangeOrderData(
        projectId: $project->id,
        description: 'Approved extra',
        amountInCents: 10000,
        status: ChangeOrderStatus::Approved,
    ), $company->id);

    $this->assertNotNull($changeOrder->approved_at);
});
