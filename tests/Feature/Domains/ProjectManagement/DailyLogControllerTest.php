<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyLogControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createDailyLog(Company $company, Project $project, User $author): DailyLog
    {
        return DailyLog::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'user_id' => $author->id,
        ]);
    }

    public function test_a_user_can_create_a_daily_log(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/daily-logs', [
            'project_id' => $project->id,
            'log_date' => '2026-08-18',
            'work_performed' => 'Poured foundation',
        ])->assertCreated();

        $this->assertDatabaseHas('daily_logs', [
            'company_id' => $company->id,
            'work_performed' => 'Poured foundation',
        ]);
    }

    public function test_a_user_can_list_their_daily_logs(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        DailyLog::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/daily-logs')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_delete_their_daily_log(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $dailyLog = $this->createDailyLog($company, $project, $user);

        $this->actingAs($user)
            ->deleteJson('/api/daily-logs/'.$dailyLog->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('daily_logs', ['id' => $dailyLog->id]);
    }

    public function test_a_user_cannot_view_another_tenants_daily_log(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
        $authorB = User::factory()->create(['company_id' => $companyB->id]);
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $dailyLogB = $this->createDailyLog($companyB, $projectB, $authorB);

        $this->actingAs($user)
            ->getJson('/api/daily-logs/'.$dailyLogB->uuid)
            ->assertNotFound();
    }
}
