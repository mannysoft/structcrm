<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_page_requires_authentication(): void
    {
        $this->get('/clients')->assertRedirect('/login');
    }

    public function test_clients_page_displays_clients(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        Client::factory()->create(['company_id' => $company->id, 'name' => 'Jane Doe']);

        $this->actingAs($user)
            ->get('/clients')
            ->assertOk()
            ->assertSee('Jane Doe');
    }
}
