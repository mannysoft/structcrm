<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Document;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
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

    public function test_a_user_can_upload_a_document(): void
    {
        Storage::fake();

        $company = Company::factory()->create();
        $project = $this->makeProject($company);
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
    }

    public function test_a_user_can_list_their_documents(): void
    {
        $company = Company::factory()->create();
        $project = $this->makeProject($company);
        $user = User::factory()->create(['company_id' => $company->id]);

        Document::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/documents')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_delete_their_document(): void
    {
        $company = Company::factory()->create();
        $project = $this->makeProject($company);
        $user = User::factory()->create(['company_id' => $company->id]);
        $document = Document::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/documents/'.$document->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('documents', ['id' => $document->id]);
    }

    public function test_a_user_cannot_view_another_tenants_document(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $projectB = $this->makeProject($companyB);
        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $documentB = Document::factory()->create([
            'company_id' => $companyB->id,
            'project_id' => $projectB->id,
        ]);

        $this->actingAs($userA)
            ->getJson('/api/documents/'.$documentB->uuid)
            ->assertNotFound();
    }
}
