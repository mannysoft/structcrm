<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Actions\DeleteDailyLogAction;
use App\Domains\ProjectManagement\Models\DailyLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteDailyLog implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a daily site log. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a daily log removes the site record of work performed.');
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
            $log = $this->findRecord(DailyLog::class, $validated['log'], 'daily log');
            $this->authorize('delete', $log);

            $summary = $this->dailyLogSummary($log->load(['project', 'author']));

            app(DeleteDailyLogAction::class)->execute($log);

            return [
                'message' => 'Daily log deleted.',
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
            'log' => $schema->string()->description('Daily log UUID.')->required(),
        ];
    }
}
