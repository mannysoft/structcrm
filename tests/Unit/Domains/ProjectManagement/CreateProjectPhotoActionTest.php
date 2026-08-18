<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateProjectPhotoAction;
use App\Domains\ProjectManagement\DTOs\ProjectPhotoData;
use App\Domains\ProjectManagement\Enums\PhotoCategory;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectPhotoActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_project_photo(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $photo = app(CreateProjectPhotoAction::class)->execute(new ProjectPhotoData(
            projectId: $project->id,
            category: PhotoCategory::During,
            filePath: 'project-photos/site.jpg',
            caption: 'Foundation progress',
        ), $company->id, $user->id);

        $this->assertSame($project->id, $photo->project_id);
        $this->assertSame($user->id, $photo->user_id);
        $this->assertSame(PhotoCategory::During, $photo->category);
        $this->assertSame('project-photos/site.jpg', $photo->file_path);

        $this->assertDatabaseHas('project_photos', [
            'id' => $photo->id,
            'caption' => 'Foundation progress',
        ]);
    }
}
