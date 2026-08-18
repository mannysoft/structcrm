<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createChangeOrder(Company $company, Project $project): ChangeOrder
    {
        return ChangeOrder::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);
    }

    public function test_a_user_can_create_a_change_order(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/change-orders', [
            'project_id' => $project->id,
            'description' => 'Extra work',
            'amount' => 5000.00,
            'status' => ChangeOrderStatus::Pending->value,
        ])->assertCreated();

        $this->assertDatabaseHas('change_orders', [
            'company_id' => $company->id,
            'description' => 'Extra work',
        ]);
    }

    public function test_a_user_can_list_their_change_orders(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        ChangeOrder::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/change-orders')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_delete_their_change_order(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $changeOrder = $this->createChangeOrder($company, $project);

        $this->actingAs($user)
            ->deleteJson('/api/change-orders/'.$changeOrder->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('change_orders', ['id' => $changeOrder->id]);
    }

    public function test_a_user_cannot_view_another_tenants_change_order(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $changeOrderB = $this->createChangeOrder($companyB, $projectB);

        $this->actingAs($user)
            ->getJson('/api/change-orders/'.$changeOrderB->uuid)
            ->assertNotFound();
    }
}
