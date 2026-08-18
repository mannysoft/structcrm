<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_logs_page_requires_authentication(): void
    {
        $this->get('/daily-logs')->assertRedirect('/login');
    }

    public function test_daily_logs_page_displays_logs(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        DailyLog::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'user_id' => $user->id,
            'work_performed' => 'Poured foundation',
        ]);

        $this->actingAs($user)
            ->get('/daily-logs')
            ->assertOk()
            ->assertSee('Poured foundation');
    }
}
