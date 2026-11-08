<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\DeleteProjectPhotoAction;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteProjectPhoto implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a project photo. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a project photo removes the site evidence from the project gallery.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'photo' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $photo = $this->findRecord(ProjectPhoto::class, $validated['photo'], 'photo');
            $this->authorize('delete', $photo);

            $summary = $this->photoSummary($photo->load(['project', 'uploader']));

            app(DeleteProjectPhotoAction::class)->execute($photo);

            return [
                'message' => 'Project photo deleted.',
                'photo' => $summary,
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
        ];
    }
}
