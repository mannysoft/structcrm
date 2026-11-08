<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\DeleteEquipmentAction;
use App\Domains\ProjectManagement\Models\Equipment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteEquipment implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete an equipment record and its maintenance history. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting equipment removes it and its maintenance history from the register.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'equipment' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $equipment = $this->findRecord(Equipment::class, $validated['equipment'], 'equipment', ['name']);
            $this->authorize('delete', $equipment);

            $summary = $this->equipmentSummary($equipment);

            app(DeleteEquipmentAction::class)->execute($equipment);

            return [
                'message' => 'Equipment deleted.',
                'equipment' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'equipment' => $schema->string()->description('Equipment UUID or exact name.')->required(),
        ];
    }
}
