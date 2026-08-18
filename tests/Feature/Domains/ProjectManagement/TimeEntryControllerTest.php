<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\TimeEntry;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(Company $company): Project
    {
        $client = Client::factory()->create(['company_id' => $company->id]);

        return Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);
    }

    public function test_a_user_can_clock_in(): void
    {
        $company = Company::factory()->create();
        $project = $this->makeProject($company);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/time-entries', [
            'project_id' => $project->id,
            'clock_in_at' => '2026-08-18 08:00:00',
            'notes' => 'Started framing',
        ])->assertCreated();

        $this->assertDatabaseHas('time_entries', [
            'company_id' => $company->id,
            'project_id' => $project->id,
            'user_id' => $user->id,
            'notes' => 'Started framing',
        ]);
    }

    public function test_a_user_can_list_their_time_entries(): void
    {
        $company = Company::factory()->create();
        $project = $this->makeProject($company);
        $user = User::factory()->create(['company_id' => $company->id]);

        TimeEntry::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/time-entries')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_delete_their_time_entry(): void
    {
        $company = Company::factory()->create();
        $project = $this->makeProject($company);
        $user = User::factory()->create(['company_id' => $company->id]);
        $entry = TimeEntry::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/time-entries/'.$entry->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('time_entries', ['id' => $entry->id]);
    }

    public function test_a_user_cannot_view_another_tenants_time_entry(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $projectB = $this->makeProject($companyB);
        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $entryB = TimeEntry::factory()->create([
            'company_id' => $companyB->id,
            'project_id' => $projectB->id,
            'user_id' => User::factory()->create(['company_id' => $companyB->id])->id,
        ]);

        $this->actingAs($userA)
            ->getJson('/api/time-entries/'.$entryB->uuid)
            ->assertNotFound();
    }
}
