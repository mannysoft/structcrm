<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\UpdateEstimateAction;
use App\Domains\Sales\DTOs\EstimateData;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\Models\Estimate;
use App\Domains\Sales\Models\Lead;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateEstimate implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update an estimate: title, status, tax rate, validity date, lead or project. Supply items to replace the existing line items; omit items to keep them.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'estimate' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'valid_until' => ['nullable', 'string'],
            'lead' => ['nullable', 'string'],
            'project' => ['nullable', 'string'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.description' => ['required_with:items', 'string', 'max:500'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $estimate = $this->findRecord(Estimate::class, $validated['estimate'], 'estimate', ['estimate_number']);
            $this->authorize('update', $estimate);

            try {
                $status = filled($validated['status'] ?? null)
                    ? EstimateStatus::from($validated['status'])
                    : $estimate->status;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported estimate status. Use one of: '.implode(', ', array_column(EstimateStatus::cases(), 'value')).'.');
            }

            $leadId = filled($validated['lead'] ?? null)
                ? $this->findRecord(Lead::class, $validated['lead'], 'lead', ['email'])->id
                : $estimate->lead_id;

            $projectId = filled($validated['project'] ?? null)
                ? $this->findRecord(Project::class, $validated['project'], 'project', ['project_number'])->id
                : $estimate->project_id;

            $items = filled($validated['items'] ?? null)
                ? collect($validated['items'])->map(fn (array $item) => [
                    'description' => $item['description'],
                    'quantity' => (float) $item['quantity'],
                    'unit_price' => $this->toCents($item['unit_price']),
                ])->all()
                : $estimate->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->all();

            $taxRate = isset($validated['tax_rate'])
                ? (float) $validated['tax_rate']
                : ($estimate->subtotal > 0 ? round($estimate->tax_amount / $estimate->subtotal * 100, 4) : 0.0);

            $updated = app(UpdateEstimateAction::class)->execute($estimate, new EstimateData(
                title: (string) ($validated['title'] ?? $estimate->title),
                status: $status,
                items: $items,
                leadId: $leadId,
                projectId: $projectId,
                validUntil: filled($validated['valid_until'] ?? null)
                    ? $this->parseDate($validated['valid_until'])
                    : $estimate->valid_until,
                taxRate: $taxRate,
            ));

            return [
                'message' => 'Estimate updated.',
                'estimate' => $this->estimateSummary($updated->loadCount('items')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'estimate' => $schema->string()->description('Estimate UUID or estimate number.')->required(),
            'title' => $schema->string()->description('New title.'),
            'status' => $schema->string()->enum(array_column(EstimateStatus::cases(), 'value'))->description('New status.'),
            'tax_rate' => $schema->number()->description('New tax rate percentage.')->min(0)->max(100),
            'valid_until' => $schema->string()->description('New validity date (YYYY-MM-DD).'),
            'lead' => $schema->string()->description('New lead UUID or email.'),
            'project' => $schema->string()->description('New project UUID or project number.'),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line item description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
            ]))->description('Replacement line items. Omit to keep the current items.'),
        ];
    }
}
