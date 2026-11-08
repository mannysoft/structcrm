<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\DeleteMaintenanceLogAction;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteMaintenanceLog implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a maintenance log. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a maintenance log removes the service history and cost record.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'log' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $log = $this->findRecord(MaintenanceLog::class, $validated['log'], 'maintenance log');
            $this->authorize('delete', $log);

            $summary = $this->maintenanceLogSummary($log->load('equipment'));

            app(DeleteMaintenanceLogAction::class)->execute($log);

            return [
                'message' => 'Maintenance log deleted.',
                'log' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'log' => $schema->string()->description('Maintenance log UUID.')->required(),
        ];
    }
}
