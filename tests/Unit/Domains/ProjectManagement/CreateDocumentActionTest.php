<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateDocumentAction;
use App\Domains\ProjectManagement\DTOs\DocumentData;
use App\Domains\ProjectManagement\Enums\DocumentCategory;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;

test('it creates a document', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $document = app(CreateDocumentAction::class)->execute(new DocumentData(
        projectId: $project->id,
        name: 'Site Permit',
        category: DocumentCategory::Permit,
        filePath: 'documents/permit.pdf',
    ), $company->id);

    $this->assertSame($project->id, $document->project_id);
    $this->assertSame(DocumentCategory::Permit, $document->category);
    $this->assertSame('documents/permit.pdf', $document->file_path);

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'name' => 'Site Permit',
    ]);
});
