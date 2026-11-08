<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Actions\DeleteProgressReportAction;
use App\Domains\Finance\Models\ProgressReport;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class DeleteProgressReport implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Delete a progress report and its accomplishment items. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a progress report removes substantiated accomplishment records.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'progress_report' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $report = $this->findRecord(ProgressReport::class, $validated['progress_report'], 'progress report', ['report_number']);
            $this->authorize('delete', $report);

            $summary = $this->progressReportSummary($report->load('items'));

            app(DeleteProgressReportAction::class)->execute($report);

            return [
                'message' => 'Progress report deleted.',
                'progress_report' => $summary,
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'progress_report' => $schema->string()->description('Progress report UUID or report number.')->required(),
        ];
    }
}
