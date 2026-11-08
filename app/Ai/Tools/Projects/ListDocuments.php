<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Enums\DocumentCategory;
use App\Domains\ProjectManagement\Models\Document;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListDocuments implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List project documents (blueprints, permits, contracts, specifications) with their storage paths and categories.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Document::class);

            $search = trim((string) ($request['search'] ?? ''));

            $query = Document::query()
                ->with('project')
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where('name', 'like', "%{$search}%"))
                ->when(filled($request['category'] ?? null), fn ($builder) => $builder->where('category', $request['category']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $documents = $query->latest()->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $documents->count(),
                'documents' => $documents->map(fn (Document $document) => $this->documentSummary($document))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('Optional project UUID or project number.'),
            'search' => $schema->string()->description('Optional document name fragment.'),
            'category' => $schema->string()
                ->enum(array_column(DocumentCategory::cases(), 'value'))
                ->description('Optional category filter.'),
            'limit' => $schema->integer()->description('Maximum documents to return (default 25, max 100).'),
        ];
    }
}
