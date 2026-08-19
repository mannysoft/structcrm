<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;

test('a user can create a supplier', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/suppliers', [
        'name' => 'Acme Materials',
        'email' => 'sales@acme.test',
    ])->assertCreated();

    $this->assertDatabaseHas('suppliers', [
        'company_id' => $company->id,
        'name' => 'Acme Materials',
    ]);
});

test('a user can list their suppliers', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    Supplier::factory()->count(2)->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->getJson('/api/suppliers')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can update their supplier', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->putJson('/api/suppliers/'.$supplier->uuid, [
            'name' => 'Updated Supplier',
        ])
        ->assertOk()
        ->assertJsonPath('name', 'Updated Supplier');
});

test('a user can delete their supplier', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->deleteJson('/api/suppliers/'.$supplier->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});

test('a user cannot view another tenants supplier', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $supplierB = Supplier::factory()->create(['company_id' => $companyB->id]);

    $this->actingAs($user)
        ->getJson('/api/suppliers/'.$supplierB->uuid)
        ->assertNotFound();
});
