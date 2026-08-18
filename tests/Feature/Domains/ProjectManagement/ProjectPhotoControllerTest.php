<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectPhotoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(Company $company): Project
    {
        $client = Client::factory()->create(['company_id' => $company->id]);

        return Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);
    }

    public function test_a_user_can_upload_a_photo(): void
    {
        Storage::fake();

        $company = Company::factory()->create();
        $project = $this->makeProject($company);
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
    }

    public function test_a_user_can_list_their_photos(): void
    {
        $company = Company::factory()->create();
        $project = $this->makeProject($company);
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
    }

    public function test_a_user_can_delete_their_photo(): void
    {
        $company = Company::factory()->create();
        $project = $this->makeProject($company);
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
    }

    public function test_a_user_cannot_view_another_tenants_photo(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $projectB = $this->makeProject($companyB);
        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $photoB = ProjectPhoto::factory()->create([
            'company_id' => $companyB->id,
            'project_id' => $projectB->id,
            'user_id' => User::factory()->create(['company_id' => $companyB->id])->id,
        ]);

        $this->actingAs($userA)
            ->getJson('/api/project-photos/'.$photoB->uuid)
            ->assertNotFound();
    }
}
