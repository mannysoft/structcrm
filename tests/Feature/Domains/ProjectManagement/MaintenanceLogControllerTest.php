<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use App\Models\User;

test('a user can create a maintenance log', function () {

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
});

test('a user can list their maintenance logs', function () {

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
});

test('a user can delete their maintenance log', function () {

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
});

test('a user cannot view another tenants maintenance log', function () {

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
});
