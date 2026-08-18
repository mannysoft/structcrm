<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_a_maintenance_log(): void
    {
        $company = Company::factory()->create();
        $equipment = Equipment::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/maintenance-logs', [
            'equipment_id' => $equipment->id,
            'service_date' => '2026-08-15',
            'description' => 'Replaced hydraulic hose',
            'cost' => 250.50,
        ])->assertCreated();

        $this->assertDatabaseHas('maintenance_logs', [
            'company_id' => $company->id,
            'equipment_id' => $equipment->id,
            'description' => 'Replaced hydraulic hose',
            'cost_amount' => 25050,
        ]);
    }

    public function test_a_user_can_list_their_maintenance_logs(): void
    {
        $company = Company::factory()->create();
        $equipment = Equipment::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        MaintenanceLog::factory()->count(2)->create([
            'company_id' => $company->id,
            'equipment_id' => $equipment->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/maintenance-logs')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_delete_their_maintenance_log(): void
    {
        $company = Company::factory()->create();
        $equipment = Equipment::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $log = MaintenanceLog::factory()->create([
            'company_id' => $company->id,
            'equipment_id' => $equipment->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/maintenance-logs/'.$log->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('maintenance_logs', ['id' => $log->id]);
    }

    public function test_a_user_cannot_view_another_tenants_maintenance_log(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $equipmentB = Equipment::factory()->create(['company_id' => $companyB->id]);
        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $logB = MaintenanceLog::factory()->create([
            'company_id' => $companyB->id,
            'equipment_id' => $equipmentB->id,
        ]);

        $this->actingAs($userA)
            ->getJson('/api/maintenance-logs/'.$logB->uuid)
            ->assertNotFound();
    }
}
