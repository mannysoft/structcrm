<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_a_client(): void
    {
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
    }

    public function test_a_user_can_list_their_clients(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Client::factory()->count(2)->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->getJson('/api/clients')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_view_their_client(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->getJson('/api/clients/'.$client->uuid)
            ->assertOk()
            ->assertJsonPath('name', $client->name);
    }

    public function test_a_user_cannot_view_another_tenants_client(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($user)
            ->getJson('/api/clients/'.$clientB->uuid)
            ->assertNotFound();
    }

    public function test_a_user_can_update_their_client(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->putJson('/api/clients/'.$client->uuid, [
                'name' => 'Updated Name',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Updated Name');
    }

    public function test_a_user_can_delete_their_client(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->deleteJson('/api/clients/'.$client->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_a_user_cannot_delete_another_tenants_client(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($user)
            ->deleteJson('/api/clients/'.$clientB->uuid)
            ->assertNotFound();
    }
}
