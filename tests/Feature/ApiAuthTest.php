<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_staff_user_can_issue_a_token(): void
    {
        $company = Company::factory()->create();
        User::factory()->create([
            'company_id' => $company->id,
            'email' => 'staff@example.test',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'staff@example.test',
            'password' => 'secret123',
            'device_name' => 'Test Device',
        ]);

        $response->assertCreated()->assertJsonStructure(['token']);
    }

    public function test_a_client_user_cannot_issue_a_token(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);

        User::factory()->client()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'email' => 'client@example.test',
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'client@example.test',
            'password' => 'secret123',
            'device_name' => 'Test Device',
        ])->assertUnprocessable();
    }

    public function test_unauthenticated_api_requests_are_rejected(): void
    {
        $this->getJson('/api/clients')->assertUnauthorized();
    }

    public function test_a_token_can_access_protected_routes(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'staff@example.test',
            'password' => Hash::make('secret123'),
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'staff@example.test',
            'password' => 'secret123',
            'device_name' => 'Test Device',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/clients')
            ->assertOk();
    }

    public function test_logout_revokes_the_token(): void
    {
        $company = Company::factory()->create();
        User::factory()->create([
            'company_id' => $company->id,
            'email' => 'staff@example.test',
            'password' => Hash::make('secret123'),
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'staff@example.test',
            'password' => 'secret123',
            'device_name' => 'Test Device',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Forget cached guards so the next request re-resolves the (now deleted) token.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/clients')
            ->assertUnauthorized();
    }
}
