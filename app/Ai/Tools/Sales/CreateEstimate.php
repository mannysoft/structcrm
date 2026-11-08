<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateEstimateAction;
use App\Domains\Sales\DTOs\EstimateData;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\Models\Estimate;
use App\Domains\Sales\Models\Lead;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateEstimate implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a priced estimate for a lead or project. Line item unit prices are in major currency units; the estimate number and totals are generated automatically.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lead' => ['nullable', 'string'],
            'project' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'valid_until' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Estimate::class);

            try {
                $status = filled($validated['status'] ?? null)
                    ? EstimateStatus::from($validated['status'])
                    : EstimateStatus::Draft;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported estimate status. Use one of: '.implode(', ', array_column(EstimateStatus::cases(), 'value')).'.');
            }

            $leadId = null;

            if (filled($validated['lead'] ?? null)) {
                $leadId = $this->findRecord(Lead::class, $validated['lead'], 'lead', ['email'])->id;
            }

            $projectId = null;

            if (filled($validated['project'] ?? null)) {
                $projectId = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number'])->id;
            }

            $items = collect($validated['items'])->map(fn (array $item) => [
                'description' => $item['description'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => $this->toCents($item['unit_price']),
            ])->all();

            $estimate = app(CreateEstimateAction::class)->execute(
                new EstimateData(
                    title: $validated['title'],
                    status: $status,
                    items: $items,
                    leadId: $leadId,
                    projectId: $projectId,
                    validUntil: filled($validated['valid_until'] ?? null) ? $this->parseDate($validated['valid_until']) : null,
                    taxRate: (float) ($validated['tax_rate'] ?? 0),
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Estimate created.',
                'estimate' => $this->estimateSummary($estimate->loadCount('items')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Estimate title.')->required(),
            'items' => $schema->array()->items($schema->object([
                'description' => $schema->string()->description('Line item description.')->required(),
                'quantity' => $schema->number()->description('Quantity.')->required(),
                'unit_price' => $schema->number()->description('Unit price in major currency units.')->required(),
            ]))->description('Estimate line items.')->required(),
            'lead' => $schema->string()->description('Optional lead UUID or email.'),
            'project' => $schema->string()->description('Optional project UUID or project number.'),
            'status' => $schema->string()->enum(array_column(EstimateStatus::cases(), 'value'))->description('Defaults to draft.'),
            'tax_rate' => $schema->number()->description('Tax rate percentage applied to the subtotal.')->min(0)->max(100),
            'valid_until' => $schema->string()->description('Optional validity date (YYYY-MM-DD).'),
        ];
    }
}
