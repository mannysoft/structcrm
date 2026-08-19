<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;

test('a user can view their own company', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->getJson('/api/companies/'.$company->uuid)
        ->assertOk()
        ->assertJsonPath('name', $company->name);
});

test('a user can list their own company', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->getJson('/api/companies')
        ->assertOk()
        ->assertJsonPath('name', $company->name);
});

test('a user cannot view another tenants company', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $companyA->id]);

    $this->actingAs($user)
        ->getJson('/api/companies/'.$companyB->uuid)
        ->assertForbidden();
});

test('a user can update their own company', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->putJson('/api/companies/'.$company->uuid, [
            'name' => 'Updated Name',
            'currency' => 'EUR',
        ])
        ->assertOk()
        ->assertJsonPath('name', 'Updated Name')
        ->assertJsonPath('currency', 'EUR');
});

test('a user cannot update another tenants company', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $companyA->id]);

    $this->actingAs($user)
        ->putJson('/api/companies/'.$companyB->uuid, [
            'name' => 'Hijacked Name',
            'currency' => 'EUR',
        ])
        ->assertForbidden();
});

test('a user cannot delete another tenants company', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $companyA->id]);

    $this->actingAs($user)
        ->deleteJson('/api/companies/'.$companyB->uuid)
        ->assertForbidden();
});

test('a user can delete their own company', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->deleteJson('/api/companies/'.$company->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});
