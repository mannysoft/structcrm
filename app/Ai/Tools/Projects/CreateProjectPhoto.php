<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Actions\CreateProjectPhotoAction;
use App\Domains\ProjectManagement\DTOs\ProjectPhotoData;
use App\Domains\ProjectManagement\Enums\PhotoCategory;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateProjectPhoto implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Register a project photo that the user has already uploaded: project, category (before, during, after, inspection, safety), storage path and optional caption.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'category' => ['required', 'string'],
            'file_path' => ['required', 'string', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', ProjectPhoto::class);

            try {
                $category = PhotoCategory::from($validated['category']);
            } catch (Throwable) {
                throw new ToolFailure(
                    'Unsupported photo category. Use one of: '.implode(', ', array_column(PhotoCategory::cases(), 'value')).'.'
                );
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $photo = app(CreateProjectPhotoAction::class)->execute(
                new ProjectPhotoData(
                    projectId: $project->id,
                    category: $category,
                    filePath: $validated['file_path'],
                    caption: $validated['caption'] ?? null,
                ),
                $this->companyId(),
                $this->user->id,
            );

            return [
                'message' => 'Project photo registered.',
                'photo' => $this->photoSummary($photo->fresh(['project', 'uploader'])),
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
            'category' => $schema->string()
                ->enum(array_column(PhotoCategory::cases(), 'value'))
                ->description('Photo category.')
                ->required(),
            'file_path' => $schema->string()->description('Path of the already-uploaded image in storage.')->required(),
            'caption' => $schema->string()->description('Optional caption.'),
        ];
    }
}
