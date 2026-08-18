<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\DTOs\ClientData;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_client_scoped_to_a_company(): void
    {
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
    }

    public function test_it_lists_only_clients_for_the_given_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Client::factory()->count(2)->create(['company_id' => $companyA->id]);
        Client::factory()->create(['company_id' => $companyB->id]);

        $service = app(ClientService::class);

        $this->assertCount(2, $service->list($companyA->id));
        $this->assertCount(1, $service->list($companyB->id));
    }

    public function test_it_updates_a_client(): void
    {
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
    }

    public function test_it_soft_deletes_a_client(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);

        app(ClientService::class)->delete($client);

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_find_returns_null_for_another_tenants_client(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);

        $this->assertNull(app(ClientService::class)->find($clientB->uuid, $companyA->id));
    }
}
