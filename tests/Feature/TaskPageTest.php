<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_page_requires_authentication(): void
    {
        $this->get('/tasks')->assertRedirect('/login');
    }

    public function test_tasks_page_displays_tasks(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'title' => 'Pour foundation',
        ]);

        $this->actingAs($user)
            ->get('/tasks')
            ->assertOk()
            ->assertSee('Pour foundation');
    }
}
