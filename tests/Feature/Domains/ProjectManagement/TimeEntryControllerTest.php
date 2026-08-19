<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\TimeEntry;
use App\Models\User;

test('a user can clock in', function () {

    $company = Company::factory()->create();
    $project = makeProject($company);
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
});

test('a user can list their time entries', function () {

    $company = Company::factory()->create();
    $project = makeProject($company);
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
});

test('a user can delete their time entry', function () {

    $company = Company::factory()->create();
    $project = makeProject($company);
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
});

test('a user cannot view another tenants time entry', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $projectB = makeProject($companyB);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $entryB = TimeEntry::factory()->create([
        'company_id' => $companyB->id,
        'project_id' => $projectB->id,
        'user_id' => User::factory()->create(['company_id' => $companyB->id])->id,
    ]);

    $this->actingAs($userA)
        ->getJson('/api/time-entries/'.$entryB->uuid)
        ->assertNotFound();
});
