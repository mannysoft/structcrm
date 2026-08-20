<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

function progressPayload(Project $project): array
{
    return [
        'project_id' => $project->id,
        'report_date' => '2026-08-20',
        'status' => ProgressReportStatus::Submitted->value,
        'items' => [
            ['description' => 'Foundation works', 'quantity' => 1, 'percent_complete' => 60, 'accomplished_amount' => 1500000],
        ],
    ];
}

test('a user can create a progress report', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/progress-reports', progressPayload($project))->assertCreated();

    $this->assertDatabaseHas('progress_reports', [
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);

    $this->assertDatabaseHas('progress_items', ['description' => 'Foundation works']);
});

test('a user can list their progress reports', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    foreach (range(1, 2) as $i) {
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        ProgressReport::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);
    }

    $this->actingAs($user)
        ->getJson('/api/progress-reports')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can update their progress report', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $report = ProgressReport::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

    $this->actingAs($user)
        ->putJson('/api/progress-reports/'.$report->uuid, progressPayload($project))
        ->assertOk()
        ->assertJsonPath('status', ProgressReportStatus::Submitted->value);
});

test('a user can delete their progress report', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $report = ProgressReport::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

    $this->actingAs($user)
        ->deleteJson('/api/progress-reports/'.$report->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('progress_reports', ['id' => $report->id]);
});

test('a user cannot access another tenants progress report', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id]);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $reportB = ProgressReport::factory()->create(['company_id' => $companyB->id, 'project_id' => $projectB->id]);

    $this->actingAs($userA)
        ->getJson('/api/progress-reports/'.$reportB->uuid)
        ->assertNotFound();
});
