<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can upload a document', function () {

    Storage::fake();

    $company = Company::factory()->create();
    $project = makeProject($company);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->post('/api/documents', [
        'project_id' => $project->id,
        'name' => 'Site Permit',
        'category' => 'permit',
        'file' => UploadedFile::fake()->create('permit.pdf', 100),
    ])->assertCreated();

    $this->assertDatabaseHas('documents', [
        'company_id' => $company->id,
        'project_id' => $project->id,
        'name' => 'Site Permit',
        'category' => 'permit',
    ]);
});

test('a user can list their documents', function () {

    $company = Company::factory()->create();
    $project = makeProject($company);
    $user = User::factory()->create(['company_id' => $company->id]);

    Document::factory()->count(2)->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/documents')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can delete their document', function () {

    $company = Company::factory()->create();
    $project = makeProject($company);
    $user = User::factory()->create(['company_id' => $company->id]);
    $document = Document::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->deleteJson('/api/documents/'.$document->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('documents', ['id' => $document->id]);
});

test('a user cannot view another tenants document', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $projectB = makeProject($companyB);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $documentB = Document::factory()->create([
        'company_id' => $companyB->id,
        'project_id' => $projectB->id,
    ]);

    $this->actingAs($userA)
        ->getJson('/api/documents/'.$documentB->uuid)
        ->assertNotFound();
});
