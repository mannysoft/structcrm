<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Finance\Actions\CreateProgressReportAction;
use App\Domains\Finance\DTOs\ProgressReportData;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\CostCode;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateProgressReport implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a progress report for a project with accomplished quantities per line. Accomplished amounts are in major currency units.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'report_date' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.accomplished_amount' => ['required', 'numeric', 'min:0'],
            'items.*.percent_complete' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.boq_item' => ['nullable', 'string'],
            'items.*.cost_code' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', ProgressReport::class);

            try {
                $status = filled($validated['status'] ?? null)
                    ? ProgressReportStatus::from($validated['status'])
                    : ProgressReportStatus::Draft;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported progress report status. Use one of: '.implode(', ', array_column(ProgressReportStatus::cases(), 'value')).'.');
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $items = collect($validated['items'])->map(fn (array $item) => [
                'boq_item_id' => filled($item['boq_item'] ?? null)
                    ? $this->findRecord(BoqItem::class, $item['boq_item'], 'BOQ item', ['item_code'])->id
                    : null,
                'cost_code_id' => filled($item['cost_code'] ?? null)
                    ? $this->findRecord(CostCode::class, $item['cost_code'], 'cost code', ['code'])->id
                    : null,
                'description' => $item['description'],
                'quantity' => (float) $item['quantity'],
                'percent_complete' => (float) ($item['percent_complete'] ?? 0),
                'accomplished_amount' => $this->toCents($item['accomplished_amount']),
            ])->all();

            $report = app(CreateProgressReportAction::class)->execute(
                new ProgressReportData(
                    projectId: $project->id,
                    reportDate: $this->parseDate($validated['report_date']),
                    status: $status,
                    notes: $validated['notes'] ?? null,
                    items: $items,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Progress report created.',
                'progress_report' => $this->progressReportSummary($report->load('items')),
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
            'report_date' => $schema->string()->description('Report date (YYYY-MM-DD).')->required(),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Work item description.')->required(),
                'quantity' => $schema->number()->description('Accomplished quantity.')->required(),
                'accomplished_amount' => $schema->number()->description('Accomplished value in major currency units.')->required(),
                'percent_complete' => $schema->number()->description('Percent complete (0-100).'),
                'boq_item' => $schema->string()->description('Optional BOQ item UUID or item code.'),
                'cost_code' => $schema->string()->description('Optional cost code UUID or code.'),
            ]))->description('Progress line items.')->required(),
            'status' => $schema->string()->enum(array_column(ProgressReportStatus::cases(), 'value'))->description('Defaults to draft.'),
            'notes' => $schema->string()->description('Optional notes.'),
        ];
    }
}
