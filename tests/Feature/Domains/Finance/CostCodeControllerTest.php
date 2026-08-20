<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\CostCode;
use App\Models\User;

function createCostCode(Company $company): CostCode
{
    return CostCode::factory()->create([
        'company_id' => $company->id,
    ]);
}

test('a user can create a cost code', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/cost-codes', [
        'code' => '100',
        'name' => 'Materials',
        'type' => CostCodeType::Material->value,
    ])->assertCreated();

    $this->assertDatabaseHas('cost_codes', [
        'company_id' => $company->id,
        'code' => '100',
        'name' => 'Materials',
    ]);
});

test('a user can list their cost codes', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    CostCode::factory()->count(2)->create([
        'company_id' => $company->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/cost-codes')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can update their cost code', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $costCode = createCostCode($company);

    $this->actingAs($user)
        ->putJson('/api/cost-codes/'.$costCode->uuid, [
            'code' => '110',
            'name' => 'Updated Materials',
            'type' => CostCodeType::Material->value,
        ])
        ->assertOk()
        ->assertJsonPath('name', 'Updated Materials');
});

test('a user can delete their cost code', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $costCode = createCostCode($company);

    $this->actingAs($user)
        ->deleteJson('/api/cost-codes/'.$costCode->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('cost_codes', ['id' => $costCode->id]);
});

test('a user cannot access another tenants cost code', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $costCodeB = createCostCode($companyB);

    $this->actingAs($userA)
        ->getJson('/api/cost-codes/'.$costCodeB->uuid)
        ->assertNotFound();
});
