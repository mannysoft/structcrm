<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\CreateBoqItemAction;
use App\Domains\Finance\DTOs\BoqItemData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;

test('it creates a boq item scoped to a company', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $boqItem = app(CreateBoqItemAction::class)->execute(new BoqItemData(
        projectId: $project->id,
        description: 'Concrete slab',
        quantity: 12.5,
        unit: 'cu.m.',
        unitPrice: Money::fromDecimal(5000, 'PHP'),
    ), $company->id);

    $this->assertSame('Concrete slab', $boqItem->description);
    $this->assertSame(12.5, $boqItem->quantity);
    $this->assertSame(500000, $boqItem->unit_price);
    $this->assertSame(6250000, $boqItem->total);
    $this->assertSame('PHP', $boqItem->currency);
    $this->assertNotNull($boqItem->uuid);

    $this->assertDatabaseHas('boq_items', [
        'id' => $boqItem->id,
        'description' => 'Concrete slab',
    ]);
});
