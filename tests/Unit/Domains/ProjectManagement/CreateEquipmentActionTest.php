<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateEquipmentAction;
use App\Domains\ProjectManagement\DTOs\EquipmentData;
use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;

test('it creates equipment', function () {

    $company = Company::factory()->create();

    $equipment = app(CreateEquipmentAction::class)->execute(new EquipmentData(
        name: 'Excavator 320',
        type: EquipmentType::HeavyMachinery,
        status: EquipmentStatus::Available,
    ), $company->id);

    $this->assertSame('Excavator 320', $equipment->name);
    $this->assertSame(EquipmentType::HeavyMachinery, $equipment->type);
    $this->assertSame(EquipmentStatus::Available, $equipment->status);

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'name' => 'Excavator 320',
    ]);
});
