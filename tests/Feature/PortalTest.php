<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_requires_client_authentication(): void
    {
        $this->get('/portal')->assertRedirect('/portal/login');
    }

    public function test_staff_user_cannot_access_portal(): void
    {
        $company = Company::factory()->create();
        $staff = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($staff)->get('/portal')->assertRedirect('/portal/login');
    }

    public function test_client_sees_only_their_projects(): void
    {
        $company = Company::factory()->create();
        $clientA = Client::factory()->create(['company_id' => $company->id]);
        $clientB = Client::factory()->create(['company_id' => $company->id]);

        Project::factory()->create(['company_id' => $company->id, 'client_id' => $clientA->id, 'title' => 'Alpha Project']);
        Project::factory()->create(['company_id' => $company->id, 'client_id' => $clientB->id, 'title' => 'Beta Project']);

        $clientUser = User::factory()->client()->create([
            'company_id' => $company->id,
            'client_id' => $clientA->id,
        ]);

        $this->actingAs($clientUser, 'client')
            ->get('/portal')
            ->assertOk()
            ->assertSee('Alpha Project')
            ->assertDontSee('Beta Project');
    }

    public function test_client_does_not_see_other_clients_change_orders(): void
    {
        $company = Company::factory()->create();
        $clientA = Client::factory()->create(['company_id' => $company->id]);
        $clientB = Client::factory()->create(['company_id' => $company->id]);

        $projectA = Project::factory()->create(['company_id' => $company->id, 'client_id' => $clientA->id]);
        $projectB = Project::factory()->create(['company_id' => $company->id, 'client_id' => $clientB->id]);

        ChangeOrder::factory()->create(['company_id' => $company->id, 'project_id' => $projectA->id, 'status' => ChangeOrderStatus::Pending, 'description' => 'Alpha scope change']);
        ChangeOrder::factory()->create(['company_id' => $company->id, 'project_id' => $projectB->id, 'status' => ChangeOrderStatus::Pending, 'description' => 'Beta scope change']);

        Invoice::factory()->create(['company_id' => $company->id, 'project_id' => $projectA->id, 'status' => InvoiceStatus::Sent]);
        Invoice::factory()->create(['company_id' => $company->id, 'project_id' => $projectB->id, 'status' => InvoiceStatus::Sent]);

        $clientUser = User::factory()->client()->create([
            'company_id' => $company->id,
            'client_id' => $clientA->id,
        ]);

        $this->actingAs($clientUser, 'client')
            ->get('/portal')
            ->assertOk()
            ->assertSee('Alpha scope change')
            ->assertDontSee('Beta scope change');
    }

    public function test_client_guard_rejects_staff_credentials(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);

        User::factory()->create(['company_id' => $company->id, 'email' => 'staff@example.test']);
        User::factory()->client()->create(['company_id' => $company->id, 'client_id' => $client->id, 'email' => 'client@example.test']);

        $this->assertFalse(auth('client')->attempt([
            'email' => 'staff@example.test',
            'password' => 'password',
            'type' => User::TYPE_CLIENT,
        ]));

        $this->assertTrue(auth('client')->attempt([
            'email' => 'client@example.test',
            'password' => 'password',
            'type' => User::TYPE_CLIENT,
        ]));
    }

    public function test_set_password_link_requires_a_valid_signature(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $clientUser = User::factory()->client()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        $this->get('/portal/set-password/'.$clientUser->id)->assertForbidden();

        $signed = URL::temporarySignedRoute(
            'portal.set-password',
            now()->addMinutes(5),
            ['user' => $clientUser->id],
        );

        $this->get($signed)->assertOk();
    }
}
