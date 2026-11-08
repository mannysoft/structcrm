<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Actions\CreateDocumentAction;
use App\Domains\ProjectManagement\DTOs\DocumentData;
use App\Domains\ProjectManagement\Enums\DocumentCategory;
use App\Domains\ProjectManagement\Models\Document;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateDocument implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Register a document that already exists in storage against a project, with its name, category and storage path.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'file_path' => ['required', 'string', 'max:2048'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Document::class);

            try {
                $category = DocumentCategory::from($validated['category']);
            } catch (Throwable) {
                throw new ToolFailure(
                    'Unsupported document category. Use one of: '.implode(', ', array_column(DocumentCategory::cases(), 'value')).'.'
                );
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $document = app(CreateDocumentAction::class)->execute(
                new DocumentData(
                    projectId: $project->id,
                    name: $validated['name'],
                    category: $category,
                    filePath: $validated['file_path'],
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Document registered.',
                'document' => $this->documentSummary($document->fresh('project')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('Project UUID or project number.')->required(),
            'name' => $schema->string()->description('Document display name.')->required(),
            'category' => $schema->string()
                ->enum(array_column(DocumentCategory::cases(), 'value'))
                ->description('Document category.')
                ->required(),
            'file_path' => $schema->string()->description('Path of the already-uploaded file in storage.')->required(),
        ];
    }
}
