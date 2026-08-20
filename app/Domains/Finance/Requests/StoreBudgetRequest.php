<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use App\Common\ValueObjects\Money;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Budget::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'type' => ['required', Rule::enum(BudgetType::class)],
            'status' => ['required', Rule::enum(BudgetStatus::class)],
            'forecast' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.cost_code_id' => ['nullable', 'integer', Rule::exists('cost_codes', 'id')->where('company_id', $companyId)],
            'items.*.boq_item_id' => ['nullable', 'integer', Rule::exists('boq_items', 'id')->where('company_id', $companyId)],
        ];
    }

    public function toDTO(): BudgetData
    {
        $items = collect($this->validated('items'))->map(fn (array $item) => [
            'description' => $item['description'],
            'quantity' => (float) $item['quantity'],
            'unit' => $item['unit'],
            'unit_price' => (int) round((float) $item['unit_price'] * 100),
            'cost_code_id' => isset($item['cost_code_id']) ? (int) $item['cost_code_id'] : null,
            'boq_item_id' => isset($item['boq_item_id']) ? (int) $item['boq_item_id'] : null,
        ])->all();

        return new BudgetData(
            projectId: (int) $this->validated('project_id'),
            type: BudgetType::from($this->validated('type')),
            status: BudgetStatus::from($this->validated('status')),
            forecast: $this->validated('forecast') !== null
                ? Money::fromDecimal((float) $this->validated('forecast'), $this->companyCurrency())
                : null,
            items: $items,
        );
    }

    private function companyCurrency(): string
    {
        return $this->user()->company?->currency?->value ?? 'PHP';
    }
}
