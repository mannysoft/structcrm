<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

use App\Domains\Sales\DTOs\EstimateData;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\Models\Estimate;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Estimate::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(EstimateStatus::class)],
            'lead_id' => ['nullable', 'integer', Rule::exists('leads', 'id')->where('company_id', $companyId)],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'valid_until' => ['nullable', 'date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function toDTO(): EstimateData
    {
        return new EstimateData(
            title: $this->validated('title'),
            status: EstimateStatus::from($this->validated('status')),
            items: collect($this->validated('items'))->map(fn (array $item) => [
                'description' => $item['description'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (int) round((float) $item['unit_price'] * 100),
            ])->all(),
            leadId: $this->validated('lead_id') ? (int) $this->validated('lead_id') : null,
            projectId: $this->validated('project_id') ? (int) $this->validated('project_id') : null,
            validUntil: $this->validated('valid_until') ? CarbonImmutable::parse($this->validated('valid_until')) : null,
            taxRate: (float) ($this->validated('tax_rate') ?? 0),
        );
    }
}
