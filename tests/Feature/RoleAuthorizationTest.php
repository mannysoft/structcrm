<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;

function projectPayload(int $clientId): array
{

    return [
        'title' => 'New Build',
        'client_id' => $clientId,
        'status' => 'active',
        'budget' => 100000,
    ];
}

test('staff cannot create a project', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $staff = User::factory()->role('staff')->create(['company_id' => $company->id]);

    $this->actingAs($staff)
        ->postJson('/api/projects', projectPayload($client->id))
        ->assertForbidden();
});

test('project manager can create a project', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $pm = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $this->actingAs($pm)
        ->postJson('/api/projects', projectPayload($client->id))
        ->assertCreated();
});

test('accountant can create an invoice but not a project', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $accountant = User::factory()->role('accountant')->create(['company_id' => $company->id]);

    $this->actingAs($accountant)
        ->postJson('/api/projects', projectPayload($client->id))
        ->assertForbidden();

    $this->actingAs($accountant)
        ->postJson('/api/invoices', [
            'project_id' => $project->id,
            'status' => 'sent',
            'issue_date' => '2026-08-01',
            'due_date' => '2026-09-01',
            'items' => [
                ['description' => 'Labor', 'quantity' => 1, 'unit_price' => 1000],
            ],
        ])
        ->assertCreated();
});

test('foreman can create a daily log but not a project', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $foreman = User::factory()->role('foreman')->create(['company_id' => $company->id]);

    $this->actingAs($foreman)
        ->postJson('/api/projects', projectPayload($client->id))
        ->assertForbidden();

    $this->actingAs($foreman)
        ->postJson('/api/daily-logs', [
            'project_id' => $project->id,
            'log_date' => '2026-08-19',
            'work_performed' => 'Framing completed.',
        ])
        ->assertCreated();
});
