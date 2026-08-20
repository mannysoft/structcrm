<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use Illuminate\Validation\Rule;

class UpdateCostCodeRequest extends StoreCostCodeRequest
{
    public function authorize(): bool
    {
        $costCode = $this->route('cost_code');

        return $this->user()?->can('update', $costCode) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;
        $costCodeId = $this->route('cost_code')->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('cost_codes', 'code')->where('company_id', $companyId)->whereNull('deleted_at')->ignore($costCodeId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(\App\Domains\Finance\Enums\CostCodeType::class)],
            'parent_id' => ['nullable', 'integer', Rule::exists('cost_codes', 'id')->where('company_id', $companyId)],
        ];
    }
}
