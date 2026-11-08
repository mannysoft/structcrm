<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\DeleteTimeEntryAction;
use App\Domains\ProjectManagement\Models\TimeEntry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteTimeEntry implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a time entry. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a time entry changes recorded labour hours.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'entry' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $entry = $this->findRecord(TimeEntry::class, $validated['entry'], 'time entry');
            $this->authorize('delete', $entry);

            $summary = $this->timeEntrySummary($entry->load(['project', 'user']));

            app(DeleteTimeEntryAction::class)->execute($entry);

            return [
                'message' => 'Time entry deleted.',
                'entry' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'entry' => $schema->string()->description('Time entry UUID.')->required(),
        ];
    }
}
