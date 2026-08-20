<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use App\Domains\Finance\DTOs\ProgressReportData;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\Finance\Models\ProgressReport;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgressReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProgressReport::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'report_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(ProgressReportStatus::class)],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.boq_item_id' => ['nullable', 'integer', Rule::exists('boq_items', 'id')->where('company_id', $companyId)],
            'items.*.cost_code_id' => ['nullable', 'integer', Rule::exists('cost_codes', 'id')->where('company_id', $companyId)],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.percent_complete' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.accomplished_amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function toDTO(): ProgressReportData
    {
        $items = collect($this->validated('items'))->map(fn (array $item) => [
            'description' => $item['description'],
            'quantity' => (float) $item['quantity'],
            'percent_complete' => (float) ($item['percent_complete'] ?? 0),
            'accomplished_amount' => (int) round((float) $item['accomplished_amount'] * 100),
            'boq_item_id' => isset($item['boq_item_id']) ? (int) $item['boq_item_id'] : null,
            'cost_code_id' => isset($item['cost_code_id']) ? (int) $item['cost_code_id'] : null,
        ])->all();

        return new ProgressReportData(
            projectId: (int) $this->validated('project_id'),
            reportDate: CarbonImmutable::parse($this->validated('report_date')),
            status: ProgressReportStatus::from($this->validated('status')),
            notes: $this->validated('notes'),
            items: $items,
        );
    }
}
