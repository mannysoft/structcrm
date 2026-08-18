<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Enums\TaskPriority;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createTask(Company $company, Project $project): Task
    {
        return Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);
    }

    public function test_a_user_can_create_a_task(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/tasks', [
            'title' => 'Pour foundation',
            'project_id' => $project->id,
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
        ])->assertCreated();

        $this->assertDatabaseHas('tasks', [
            'company_id' => $company->id,
            'title' => 'Pour foundation',
        ]);
    }

    public function test_a_user_can_list_their_tasks(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        Task::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_update_their_task(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $task = $this->createTask($company, $project);

        $this->actingAs($user)
            ->putJson('/api/tasks/'.$task->uuid, [
                'title' => 'Updated',
                'project_id' => $project->id,
                'status' => TaskStatus::Done->value,
                'priority' => TaskPriority::Low->value,
            ])
            ->assertOk()
            ->assertJsonPath('title', 'Updated');
    }

    public function test_a_user_can_delete_their_task(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $task = $this->createTask($company, $project);

        $this->actingAs($user)
            ->deleteJson('/api/tasks/'.$task->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_a_user_cannot_access_another_tenants_task(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $taskB = $this->createTask($companyB, $projectB);

        $this->actingAs($userA)
            ->getJson('/api/tasks/'.$taskB->uuid)
            ->assertNotFound();
    }
}
