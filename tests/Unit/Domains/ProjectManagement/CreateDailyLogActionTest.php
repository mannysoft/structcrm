<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateDailyLogAction;
use App\Domains\ProjectManagement\DTOs\DailyLogData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;

test('it creates a daily log', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $dailyLog = app(CreateDailyLogAction::class)->execute(new DailyLogData(
        projectId: $project->id,
        logDate: CarbonImmutable::parse('2026-08-18'),
        workPerformed: 'Poured foundation',
        crewCount: 8,
        laborHours: 64.0,
    ), $company->id, $user->id);

    $this->assertSame($project->id, $dailyLog->project_id);
    $this->assertSame($user->id, $dailyLog->user_id);
    $this->assertSame('Poured foundation', $dailyLog->work_performed);

    $this->assertDatabaseHas('daily_logs', [
        'id' => $dailyLog->id,
        'work_performed' => 'Poured foundation',
    ]);
});
