<?php

declare(strict_types=1);

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Enums\ProjectStatus;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('a user can create a project and it is scoped to their company', function () {
    // Arrange
    $company = Company::create(['name' => 'Acme Corp', 'uuid' => str()->uuid()]);
    $user = User::factory()->create(['company_id' => $company->id]);
    
    // Act
    $response = $this->actingAs($user)->postJson('/api/projects', [
        'name' => 'Highrise Construction',
        'status' => ProjectStatus::Active->value,
        'budget' => 50000.50,
    ]);
    
    // Assert
    $response->assertCreated();
    
    $this->assertDatabaseHas('projects', [
        'company_id' => $company->id,
        'name' => 'Highrise Construction',
        'budget_amount' => 5000050, // Stored in cents
    ]);
});

test('a user cannot access projects belonging to another tenant', function () {
    $companyA = Company::create(['name' => 'Company A', 'uuid' => str()->uuid()]);
    $companyB = Company::create(['name' => 'Company B', 'uuid' => str()->uuid()]);
    
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    
    // Insert bypassing scope or via factory mapping
    $projectB = Project::withoutEvents(function () use ($companyB) {
        return Project::create([
            'company_id' => $companyB->id,
            'name' => 'Project B',
            'status' => ProjectStatus::Draft,
            'budget_amount' => 1000,
            'budget_currency' => 'USD'
        ]);
    });
    
    $this->actingAs($userA);
    
    // Attempting to retrieve a project from another company via global scope
    $project = Project::find($projectB->id);
    
    expect($project)->toBeNull();
});
