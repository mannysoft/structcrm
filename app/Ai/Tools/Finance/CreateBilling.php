<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Finance\Actions\CreateBillingAction;
use App\Domains\Finance\DTOs\BillingData;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateBilling implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a progress billing for a project period with line items, retention percentage and deductions. Gross, retention and net amounts are calculated automatically.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'period_start' => ['required', 'string'],
            'period_end' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.boq_item' => ['nullable', 'string'],
            'progress_report' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'retention_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'other_deductions' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Billing::class);

            try {
                $status = filled($validated['status'] ?? null)
                    ? BillingStatus::from($validated['status'])
                    : BillingStatus::Draft;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported billing status. Use one of: '.implode(', ', array_column(BillingStatus::cases(), 'value')).'.');
            }

            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);

            $progressReportId = filled($validated['progress_report'] ?? null)
                ? $this->findRecord(ProgressReport::class, $validated['progress_report'], 'progress report', ['report_number'])->id
                : null;

            $items = collect($validated['items'])->map(fn (array $item) => [
                'boq_item_id' => filled($item['boq_item'] ?? null)
                    ? $this->findRecord(BoqItem::class, $item['boq_item'], 'BOQ item', ['item_code'])->id
                    : null,
                'description' => $item['description'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => $this->toCents($item['unit_price']),
            ])->all();

            $billing = app(CreateBillingAction::class)->execute(
                new BillingData(
                    projectId: $project->id,
                    progressReportId: $progressReportId,
                    periodStart: $this->parseDate($validated['period_start']),
                    periodEnd: $this->parseDate($validated['period_end']),
                    status: $status,
                    retentionRate: filled($validated['retention_rate'] ?? null)
                        ? ((float) $validated['retention_rate']) / 100
                        : null,
                    otherDeductions: $this->toCents($validated['other_deductions'] ?? 0),
                    dueDate: filled($validated['due_date'] ?? null) ? $this->parseDate($validated['due_date']) : null,
                    notes: $validated['notes'] ?? null,
                    currency: $this->currency(),
                    items: $items,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Billing created.',
                'billing' => $this->billingSummary($billing->load(['payments', 'items'])),
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
            'period_start' => $schema->string()->description('Billing period start (YYYY-MM-DD).')->required(),
            'period_end' => $schema->string()->description('Billing period end (YYYY-MM-DD).')->required(),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
                'boq_item' => $schema->string()->description('Optional BOQ item UUID or item code.'),
            ]))->description('Billing line items.')->required(),
            'progress_report' => $schema->string()->description('Optional progress report UUID or number.'),
            'status' => $schema->string()->enum(array_column(BillingStatus::cases(), 'value'))->description('Defaults to draft.'),
            'retention_rate' => $schema->number()->description('Retention percentage (0-100).')->min(0)->max(100),
            'other_deductions' => $schema->number()->description('Other deductions in major currency units.'),
            'due_date' => $schema->string()->description('Optional due date (YYYY-MM-DD).'),
            'notes' => $schema->string()->description('Optional notes.'),
        ];
    }
}
