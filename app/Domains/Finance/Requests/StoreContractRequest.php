<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use App\Common\ValueObjects\Money;
use App\Domains\Finance\DTOs\ContractData;
use App\Domains\Finance\Enums\ContractStatus;
use App\Domains\Finance\Models\Contract;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Contract::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'title' => ['required', 'string', 'max:255'],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(ContractStatus::class)],
            'signed_at' => ['nullable', 'date'],
        ];
    }

    public function toDTO(): ContractData
    {
        return new ContractData(
            projectId: (int) $this->validated('project_id'),
            title: $this->validated('title'),
            contractValue: Money::fromDecimal((float) $this->validated('contract_value'), $this->companyCurrency()),
            status: ContractStatus::from($this->validated('status')),
            signedAt: $this->validated('signed_at') ? CarbonImmutable::parse($this->validated('signed_at')) : null,
        );
    }

    private function companyCurrency(): string
    {
        return $this->user()->company?->currency?->value ?? 'PHP';
    }
}
