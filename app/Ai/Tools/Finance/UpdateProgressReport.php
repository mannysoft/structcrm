<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Finance\Actions\UpdateProgressReportAction;
use App\Domains\Finance\DTOs\ProgressReportData;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\CostCode;
use App\Domains\Finance\Models\ProgressReport;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateProgressReport implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a progress report: report date, status or notes. Supply items to replace the accomplishment breakdown; omit items to keep it.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'progress_report' => ['required', 'string'],
            'report_date' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.accomplished_amount' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.percent_complete' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.boq_item' => ['nullable', 'string'],
            'items.*.cost_code' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $report = $this->findRecord(ProgressReport::class, $validated['progress_report'], 'progress report', ['report_number']);
            $this->authorize('update', $report);

            try {
                $status = filled($validated['status'] ?? null)
                    ? ProgressReportStatus::from($validated['status'])
                    : $report->status;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported progress report status. Use one of: '.implode(', ', array_column(ProgressReportStatus::cases(), 'value')).'.');
            }

            $items = filled($validated['items'] ?? null)
                ? collect($validated['items'])->map(fn (array $item) => [
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
                ])->all()
                : $report->items->map(fn ($item) => [
                    'boq_item_id' => $item->boq_item_id,
                    'cost_code_id' => $item->cost_code_id,
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'percent_complete' => (float) $item->percent_complete,
                    'accomplished_amount' => (int) $item->accomplished_amount,
                ])->all();

            $updated = app(UpdateProgressReportAction::class)->execute($report, new ProgressReportData(
                projectId: $report->project_id,
                reportDate: filled($validated['report_date'] ?? null)
                    ? $this->parseDate($validated['report_date'])
                    : $report->report_date,
                status: $status,
                notes: $validated['notes'] ?? $report->notes,
                items: $items,
            ));

            return [
                'message' => 'Progress report updated.',
                'progress_report' => $this->progressReportSummary($updated->load('items')),
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
            'report_date' => $schema->string()->description('New report date (YYYY-MM-DD).'),
            'status' => $schema->string()->enum(array_column(ProgressReportStatus::cases(), 'value'))->description('New status.'),
            'notes' => $schema->string()->description('New notes.'),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'accomplished_amount' => $schema->number()->description('Accomplished value in major currency units.')->required(),
                'percent_complete' => $schema->number()->description('Completion percentage.')->min(0)->max(100),
                'boq_item' => $schema->string()->description('Optional BOQ item UUID or item code.'),
                'cost_code' => $schema->string()->description('Optional cost code UUID or code.'),
            ]))->description('Replacement accomplishment items. Omit to keep the current items.'),
        ];
    }
}
