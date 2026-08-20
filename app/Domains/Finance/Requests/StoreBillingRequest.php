<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use App\Domains\Finance\DTOs\BillingData;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Billing::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'progress_report_id' => ['nullable', 'integer', Rule::exists('progress_reports', 'id')->where('company_id', $companyId)],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date'],
            'status' => ['required', Rule::enum(BillingStatus::class)],
            'retention_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'other_deductions' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.boq_item_id' => ['nullable', 'integer', Rule::exists('boq_items', 'id')->where('company_id', $companyId)],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function toDTO(): BillingData
    {
        $items = collect($this->validated('items'))->map(fn (array $item) => [
            'description' => $item['description'],
            'quantity' => (float) $item['quantity'],
            'unit_price' => (int) round((float) $item['unit_price'] * 100),
            'boq_item_id' => isset($item['boq_item_id']) ? (int) $item['boq_item_id'] : null,
        ])->all();

        return new BillingData(
            projectId: (int) $this->validated('project_id'),
            progressReportId: $this->validated('progress_report_id') ? (int) $this->validated('progress_report_id') : null,
            periodStart: CarbonImmutable::parse($this->validated('period_start')),
            periodEnd: CarbonImmutable::parse($this->validated('period_end')),
            status: BillingStatus::from($this->validated('status')),
            retentionRate: $this->validated('retention_rate') !== null
                ? (float) $this->validated('retention_rate') / 100
                : null,
            otherDeductions: (int) round((float) ($this->validated('other_deductions') ?? 0) * 100),
            dueDate: $this->validated('due_date') ? CarbonImmutable::parse($this->validated('due_date')) : null,
            notes: $this->validated('notes'),
            currency: $this->companyCurrency(),
            items: $items,
        );
    }

    private function companyCurrency(): string
    {
        return $this->user()->company?->currency?->value ?? 'PHP';
    }
}
