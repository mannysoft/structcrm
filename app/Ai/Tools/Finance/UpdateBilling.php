<?php

declare(strict_types=1);

namespace App\Ai\Tools\Finance;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Finance\Actions\UpdateBillingAction;
use App\Domains\Finance\DTOs\BillingData;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\ProgressReport;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateBilling implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a progress billing: period, status, retention percentage, other deductions, due date, notes or linked progress report. Supply items to replace the existing line items; omit items to keep them. Gross, retention and net amounts are recalculated.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'billing' => ['required', 'string'],
            'period_start' => ['nullable', 'string'],
            'period_end' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'retention_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'other_deductions' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'progress_report' => ['nullable', 'string'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.boq_item' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $billing = $this->findRecord(Billing::class, $validated['billing'], 'billing', ['billing_number']);
            $this->authorize('update', $billing);

            try {
                $status = filled($validated['status'] ?? null)
                    ? BillingStatus::from($validated['status'])
                    : $billing->status;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported billing status. Use one of: '.implode(', ', array_column(BillingStatus::cases(), 'value')).'.');
            }

            $progressReportId = filled($validated['progress_report'] ?? null)
                ? $this->findRecord(ProgressReport::class, $validated['progress_report'], 'progress report', ['report_number'])->id
                : $billing->progress_report_id;

            $items = filled($validated['items'] ?? null)
                ? collect($validated['items'])->map(fn (array $item) => [
                    'boq_item_id' => filled($item['boq_item'] ?? null)
                        ? $this->findRecord(BoqItem::class, $item['boq_item'], 'BOQ item', ['item_code'])->id
                        : null,
                    'description' => $item['description'],
                    'quantity' => (float) $item['quantity'],
                    'unit_price' => $this->toCents($item['unit_price']),
                ])->all()
                : $billing->items->map(fn ($item) => [
                    'boq_item_id' => $item->boq_item_id,
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->all();

            $retentionRate = isset($validated['retention_rate'])
                ? ((float) $validated['retention_rate']) / 100
                : ($billing->gross_amount > 0 ? $billing->retention_amount / $billing->gross_amount : 0.0);

            $updated = app(UpdateBillingAction::class)->execute($billing, new BillingData(
                projectId: $billing->project_id,
                progressReportId: $progressReportId,
                periodStart: filled($validated['period_start'] ?? null)
                    ? $this->parseDate($validated['period_start'])
                    : $billing->period_start,
                periodEnd: filled($validated['period_end'] ?? null)
                    ? $this->parseDate($validated['period_end'])
                    : $billing->period_end,
                status: $status,
                retentionRate: $retentionRate,
                otherDeductions: isset($validated['other_deductions'])
                    ? $this->toCents($validated['other_deductions'])
                    : (int) $billing->other_deductions_amount,
                dueDate: filled($validated['due_date'] ?? null)
                    ? $this->parseDate($validated['due_date'])
                    : $billing->due_date,
                notes: $validated['notes'] ?? $billing->notes,
                currency: $billing->currency,
                items: $items,
            ));

            return [
                'message' => 'Billing updated.',
                'billing' => $this->billingSummary($updated->load(['payments', 'items'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'billing' => $schema->string()->description('Billing UUID or billing number.')->required(),
            'period_start' => $schema->string()->description('New billing period start (YYYY-MM-DD).'),
            'period_end' => $schema->string()->description('New billing period end (YYYY-MM-DD).'),
            'status' => $schema->string()->enum(array_column(BillingStatus::cases(), 'value'))->description('New status.'),
            'retention_rate' => $schema->number()->description('New retention percentage (0-100).')->min(0)->max(100),
            'other_deductions' => $schema->number()->description('New other deductions in major currency units.')->min(0),
            'due_date' => $schema->string()->description('New due date (YYYY-MM-DD).'),
            'notes' => $schema->string()->description('New notes.'),
            'progress_report' => $schema->string()->description('New progress report UUID or report number.'),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
                'boq_item' => $schema->string()->description('Optional BOQ item UUID or item code.'),
            ]))->description('Replacement line items. Omit to keep the current items.'),
        ];
    }
}
