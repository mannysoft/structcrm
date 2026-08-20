<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use App\Domains\Finance\DTOs\CostCodeData;
use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCostCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CostCode::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('cost_codes', 'code')->where('company_id', $companyId)->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CostCodeType::class)],
            'parent_id' => ['nullable', 'integer', Rule::exists('cost_codes', 'id')->where('company_id', $companyId)],
        ];
    }

    public function toDTO(): CostCodeData
    {
        return new CostCodeData(
            code: $this->validated('code'),
            name: $this->validated('name'),
            type: CostCodeType::from($this->validated('type')),
            parentId: $this->validated('parent_id') ? (int) $this->validated('parent_id') : null,
        );
    }
}
