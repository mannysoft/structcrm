<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\DTOs\EstimateData;
use App\Common\ValueObjects\Money;
use Carbon\CarbonImmutable;
use App\Domains\Sales\Models\Estimate;

class StoreEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Estimate::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(EstimateStatus::class)],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'valid_until' => ['nullable', 'date'],
        ];
    }

    public function toDTO(): EstimateData
    {
        return new EstimateData(
            title: $this->validated('title'),
            amount: Money::fromDecimal((float) $this->validated('amount')),
            status: EstimateStatus::from($this->validated('status')),
            leadId: $this->validated('lead_id') ? (int) $this->validated('lead_id') : null,
            projectId: $this->validated('project_id') ? (int) $this->validated('project_id') : null,
            validUntil: $this->validated('valid_until') ? CarbonImmutable::parse($this->validated('valid_until')) : null,
        );
    }
}
