<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateEquipmentAction;
use App\Domains\ProjectManagement\DTOs\EquipmentData;
use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEquipmentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_equipment(): void
    {
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
    }
}
