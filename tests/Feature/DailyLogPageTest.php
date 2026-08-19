<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

test('daily logs page requires authentication', function () {

    $this->get('/daily-logs')->assertRedirect('/login');
});

test('daily logs page displays logs', function () {

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
});
