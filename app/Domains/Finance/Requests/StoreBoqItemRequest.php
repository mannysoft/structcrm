<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use App\Common\ValueObjects\Money;
use App\Domains\Finance\DTOs\BoqItemData;
use App\Domains\Finance\Models\BoqItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBoqItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BoqItem::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'cost_code_id' => ['nullable', 'integer', Rule::exists('cost_codes', 'id')->where('company_id', $companyId)],
            'item_code' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function toDTO(): BoqItemData
    {
        return new BoqItemData(
            projectId: (int) $this->validated('project_id'),
            description: $this->validated('description'),
            quantity: (float) $this->validated('quantity'),
            unit: $this->validated('unit'),
            unitPrice: Money::fromDecimal((float) $this->validated('unit_price'), $this->companyCurrency()),
            costCodeId: $this->validated('cost_code_id') ? (int) $this->validated('cost_code_id') : null,
            itemCode: $this->validated('item_code'),
        );
    }

    private function companyCurrency(): string
    {
        return $this->user()->company?->currency?->value ?? 'PHP';
    }
}
