<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateTimeEntryAction;
use App\Domains\ProjectManagement\DTOs\TimeEntryData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTimeEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_time_entry(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $entry = app(CreateTimeEntryAction::class)->execute(new TimeEntryData(
            projectId: $project->id,
            clockInAt: CarbonImmutable::parse('2026-08-18 08:00:00'),
            notes: 'Started framing',
        ), $company->id, $user->id);

        $this->assertSame($project->id, $entry->project_id);
        $this->assertSame($user->id, $entry->user_id);
        $this->assertTrue($entry->isRunning());

        $this->assertDatabaseHas('time_entries', [
            'id' => $entry->id,
            'notes' => 'Started framing',
        ]);
    }
}
