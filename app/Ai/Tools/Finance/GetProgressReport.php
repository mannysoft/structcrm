<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\ProgressReport;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetProgressReport implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get one progress report with its accomplishment items, percentages and accomplished value.';
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
            $this->authorize('view', $report);

            return [
                'progress_report' => $this->progressReportSummary($report->load('items')),
                'items' => $this->progressReportItems($report),
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
