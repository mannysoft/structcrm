<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\DTOs\ClientData;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Services\ClientService;

test('it creates a client scoped to a company', function () {

    $company = Company::factory()->create();

    $client = app(ClientService::class)->create(new ClientData(
        name: 'Jane Doe',
        companyName: 'Doe Builders',
        email: 'jane@example.test',
    ), $company->id);

    $this->assertSame('Jane Doe', $client->name);
    $this->assertSame($company->id, $client->company_id);
    $this->assertNotNull($client->uuid);

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'name' => 'Jane Doe',
    ]);
});

test('it lists only clients for the given company', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    Client::factory()->count(2)->create(['company_id' => $companyA->id]);
    Client::factory()->create(['company_id' => $companyB->id]);

    $service = app(ClientService::class);

    $this->assertCount(2, $service->list($companyA->id));
    $this->assertCount(1, $service->list($companyB->id));
});

test('it updates a client', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    $updated = app(ClientService::class)->update($client, new ClientData(
        name: 'Updated Name',
        email: 'updated@example.test',
    ));

    $this->assertSame('Updated Name', $updated->name);

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'name' => 'Updated Name',
    ]);
});

test('it soft deletes a client', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    app(ClientService::class)->delete($client);

    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

test('find returns null for another tenants client', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);

    $this->assertNull(app(ClientService::class)->find($clientB->uuid, $companyA->id));
});
