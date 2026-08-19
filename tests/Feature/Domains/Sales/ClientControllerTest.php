<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;

test('a user can create a client', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/clients', [
        'name' => 'Jane Doe',
        'company_name' => 'Doe Builders',
        'email' => 'jane@example.test',
    ])->assertCreated();

    $this->assertDatabaseHas('clients', [
        'company_id' => $company->id,
        'name' => 'Jane Doe',
    ]);
});

test('a user can list their clients', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    Client::factory()->count(2)->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->getJson('/api/clients')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can view their client', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $client = Client::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->getJson('/api/clients/'.$client->uuid)
        ->assertOk()
        ->assertJsonPath('name', $client->name);
});

test('a user cannot view another tenants client', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);

    $this->actingAs($user)
        ->getJson('/api/clients/'.$clientB->uuid)
        ->assertNotFound();
});

test('a user can update their client', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $client = Client::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->putJson('/api/clients/'.$client->uuid, [
            'name' => 'Updated Name',
        ])
        ->assertOk()
        ->assertJsonPath('name', 'Updated Name');
});

test('a user can delete their client', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $client = Client::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->deleteJson('/api/clients/'.$client->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

test('a user cannot delete another tenants client', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);

    $this->actingAs($user)
        ->deleteJson('/api/clients/'.$clientB->uuid)
        ->assertNotFound();
});
