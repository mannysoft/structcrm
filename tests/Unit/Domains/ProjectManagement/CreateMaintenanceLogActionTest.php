<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateMaintenanceLogAction;
use App\Domains\ProjectManagement\DTOs\MaintenanceLogData;
use App\Domains\ProjectManagement\Models\Equipment;
use Carbon\CarbonImmutable;

test('it creates a maintenance log', function () {

    $company = Company::factory()->create();
    $equipment = Equipment::factory()->create(['company_id' => $company->id]);

    $log = app(CreateMaintenanceLogAction::class)->execute(new MaintenanceLogData(
        equipmentId: $equipment->id,
        serviceDate: CarbonImmutable::parse('2026-08-15'),
        description: 'Replaced hydraulic hose',
        cost: Money::fromDecimal(250.50),
    ), $company->id);

    $this->assertSame($equipment->id, $log->equipment_id);
    $this->assertSame(25050, $log->cost_amount);
    $this->assertSame('USD', $log->cost_currency);

    $this->assertDatabaseHas('maintenance_logs', [
        'id' => $log->id,
        'description' => 'Replaced hydraulic hose',
    ]);
});
