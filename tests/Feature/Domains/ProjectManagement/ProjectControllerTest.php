<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createProject(Company $company, Client $client): Project
    {
        return Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);
    }

    public function test_a_user_can_create_a_project_and_it_is_scoped_to_their_company(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/projects', [
            'title' => 'Highrise Construction',
            'client_id' => $client->id,
            'status' => ProjectStatus::Active->value,
            'budget' => 50000.50,
        ])->assertCreated();

        $this->assertDatabaseHas('projects', [
            'company_id' => $company->id,
            'title' => 'Highrise Construction',
            'budget_amount' => 5000050,
        ]);
    }

    public function test_a_user_can_list_their_projects(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        Project::factory()->count(2)->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_view_their_project(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $project = $this->createProject($company, $client);

        $this->actingAs($user)
            ->getJson('/api/projects/'.$project->uuid)
            ->assertOk()
            ->assertJsonPath('title', $project->title);
    }

    public function test_a_user_can_update_their_project(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $project = $this->createProject($company, $client);

        $this->actingAs($user)
            ->putJson('/api/projects/'.$project->uuid, [
                'title' => 'Updated Title',
                'client_id' => $client->id,
                'status' => ProjectStatus::Active->value,
                'budget' => 10000,
            ])
            ->assertOk()
            ->assertJsonPath('title', 'Updated Title');
    }

    public function test_a_user_can_delete_their_project(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $project = $this->createProject($company, $client);

        $this->actingAs($user)
            ->deleteJson('/api/projects/'.$project->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_a_user_cannot_view_another_tenants_project(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $projectB = $this->createProject($companyB, $clientB);

        $this->actingAs($userA)
            ->getJson('/api/projects/'.$projectB->uuid)
            ->assertNotFound();
    }

    public function test_a_user_cannot_access_projects_belonging_to_another_tenant(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $projectB = $this->createProject($companyB, $clientB);

        $this->actingAs($userA);

        $this->assertNull(Project::find($projectB->id));
    }
}
