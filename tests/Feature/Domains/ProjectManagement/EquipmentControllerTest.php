<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_equipment(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/equipment', [
            'name' => 'Excavator 320',
            'type' => 'heavy_machinery',
            'status' => 'available',
            'service_due_at' => '2026-09-01',
        ])->assertCreated();

        $this->assertDatabaseHas('equipment', [
            'company_id' => $company->id,
            'name' => 'Excavator 320',
            'status' => 'available',
        ]);
    }

    public function test_a_user_can_list_their_equipment(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Equipment::factory()->count(2)->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->getJson('/api/equipment')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_delete_their_equipment(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $equipment = Equipment::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->deleteJson('/api/equipment/'.$equipment->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('equipment', ['id' => $equipment->id]);
    }

    public function test_a_user_cannot_view_another_tenants_equipment(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $equipmentB = Equipment::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($userA)
            ->getJson('/api/equipment/'.$equipmentB->uuid)
            ->assertNotFound();
    }
}
