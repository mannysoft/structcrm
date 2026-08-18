<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_a_project_and_it_is_scoped_to_their_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->postJson('/api/projects', [
            'name' => 'Highrise Construction',
            'status' => ProjectStatus::Active->value,
            'budget' => 50000.50,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('projects', [
            'company_id' => $company->id,
            'name' => 'Highrise Construction',
            'budget_amount' => 5000050,
        ]);
    }

    public function test_a_user_cannot_access_projects_belonging_to_another_tenant(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $userA = User::factory()->create(['company_id' => $companyA->id]);

        $projectB = Project::withoutEvents(function () use ($companyB) {
            return Project::create([
                'company_id' => $companyB->id,
                'name' => 'Project B',
                'status' => ProjectStatus::Draft,
                'budget_amount' => 1000,
                'budget_currency' => 'USD',
            ]);
        });

        $this->actingAs($userA);

        $this->assertNull(Project::find($projectB->id));
    }
}
