<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Actions\UpdateProjectPhotoAction;
use App\Domains\ProjectManagement\DTOs\ProjectPhotoData;
use App\Domains\ProjectManagement\Enums\PhotoCategory;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateProjectPhoto implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a project photo: category, storage path or caption. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'photo' => ['required', 'string'],
            'category' => ['nullable', 'string'],
            'file_path' => ['nullable', 'string', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $photo = $this->findRecord(ProjectPhoto::class, $validated['photo'], 'photo');
            $this->authorize('update', $photo);

            try {
                $category = filled($validated['category'] ?? null)
                    ? PhotoCategory::from($validated['category'])
                    : $photo->category;
            } catch (Throwable) {
                throw new ToolFailure(
                    'Unsupported photo category. Use one of: '.implode(', ', array_column(PhotoCategory::cases(), 'value')).'.'
                );
            }

            $updated = app(UpdateProjectPhotoAction::class)->execute($photo, new ProjectPhotoData(
                projectId: $photo->project_id,
                category: $category,
                filePath: (string) ($validated['file_path'] ?? $photo->file_path),
                caption: $validated['caption'] ?? $photo->caption,
            ));

            return [
                'message' => 'Project photo updated.',
                'photo' => $this->photoSummary($updated->fresh(['project', 'uploader'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'photo' => $schema->string()->description('Project photo UUID.')->required(),
            'category' => $schema->string()
                ->enum(array_column(PhotoCategory::cases(), 'value'))
                ->description('New category.'),
            'file_path' => $schema->string()->description('New path of an already-uploaded image.'),
            'caption' => $schema->string()->description('New caption.'),
        ];
    }
}
