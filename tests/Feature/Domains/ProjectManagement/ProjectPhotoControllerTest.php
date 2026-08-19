<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can upload a photo', function () {

    Storage::fake();

    $company = Company::factory()->create();
    $project = makeProject($company);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->post('/api/project-photos', [
        'project_id' => $project->id,
        'category' => 'during',
        'caption' => 'Foundation progress',
        'photo' => UploadedFile::fake()->image('site.jpg'),
    ])->assertCreated();

    $this->assertDatabaseHas('project_photos', [
        'company_id' => $company->id,
        'project_id' => $project->id,
        'category' => 'during',
        'caption' => 'Foundation progress',
    ]);
});

test('a user can list their photos', function () {

    $company = Company::factory()->create();
    $project = makeProject($company);
    $user = User::factory()->create(['company_id' => $company->id]);

    ProjectPhoto::factory()->count(2)->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/project-photos')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can delete their photo', function () {

    $company = Company::factory()->create();
    $project = makeProject($company);
    $user = User::factory()->create(['company_id' => $company->id]);
    $photo = ProjectPhoto::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->deleteJson('/api/project-photos/'.$photo->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('project_photos', ['id' => $photo->id]);
});

test('a user cannot view another tenants photo', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $projectB = makeProject($companyB);
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $photoB = ProjectPhoto::factory()->create([
        'company_id' => $companyB->id,
        'project_id' => $projectB->id,
        'user_id' => User::factory()->create(['company_id' => $companyB->id])->id,
    ]);

    $this->actingAs($userA)
        ->getJson('/api/project-photos/'.$photoB->uuid)
        ->assertNotFound();
});
