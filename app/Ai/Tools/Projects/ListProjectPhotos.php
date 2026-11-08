<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Enums\PhotoCategory;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListProjectPhotos implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List project photos with category, caption and storage path, optionally filtered by project or category.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', ProjectPhoto::class);

            $query = ProjectPhoto::query()
                ->with(['project', 'uploader'])
                ->where('company_id', $this->companyId())
                ->when(filled($request['category'] ?? null), fn ($builder) => $builder->where('category', $request['category']));

            if (filled($request['project'] ?? null)) {
                $project = $this->findRecord(Project::class, (string) $request['project'], 'project', ['project_number']);
                $query->where('project_id', $project->id);
            }

            $photos = $query->latest()->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $photos->count(),
                'photos' => $photos->map(fn (ProjectPhoto $photo) => $this->photoSummary($photo))->all(),
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
            'category' => $schema->string()
                ->enum(array_column(PhotoCategory::cases(), 'value'))
                ->description('Optional photo category filter.'),
            'limit' => $schema->integer()->description('Maximum photos to return (default 25, max 100).'),
        ];
    }
}
