<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Actions\UpdateDocumentAction;
use App\Domains\ProjectManagement\DTOs\DocumentData;
use App\Domains\ProjectManagement\Enums\DocumentCategory;
use App\Domains\ProjectManagement\Models\Document;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateDocument implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a project document: name, category or the storage path of an already-uploaded file. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'document' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string'],
            'file_path' => ['nullable', 'string', 'max:2048'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $document = $this->findRecord(Document::class, $validated['document'], 'document', ['name']);
            $this->authorize('update', $document);

            try {
                $category = filled($validated['category'] ?? null)
                    ? DocumentCategory::from($validated['category'])
                    : $document->category;
            } catch (Throwable) {
                throw new ToolFailure(
                    'Unsupported document category. Use one of: '.implode(', ', array_column(DocumentCategory::cases(), 'value')).'.'
                );
            }

            $updated = app(UpdateDocumentAction::class)->execute($document, new DocumentData(
                projectId: $document->project_id,
                name: (string) ($validated['name'] ?? $document->name),
                category: $category,
                filePath: (string) ($validated['file_path'] ?? $document->file_path),
            ));

            return [
                'message' => 'Document updated.',
                'document' => $this->documentSummary($updated->fresh('project')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'document' => $schema->string()->description('Document UUID or exact name.')->required(),
            'name' => $schema->string()->description('New display name.'),
            'category' => $schema->string()
                ->enum(array_column(DocumentCategory::cases(), 'value'))
                ->description('New category.'),
            'file_path' => $schema->string()->description('New path of an already-uploaded file in storage.'),
        ];
    }
}
