<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a staff user can issue a token', function () {

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
});

test('a client user cannot issue a token', function () {

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
});

test('unauthenticated api requests are rejected', function () {

    $this->getJson('/api/clients')->assertUnauthorized();
});

test('a token can access protected routes', function () {

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
});

test('logout revokes the token', function () {

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
});
