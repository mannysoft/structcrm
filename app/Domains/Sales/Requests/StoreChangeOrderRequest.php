<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

use App\Domains\Sales\DTOs\ChangeOrderData;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChangeOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ChangeOrder::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'description' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'status' => ['required', Rule::enum(ChangeOrderStatus::class)],
        ];
    }

    public function toDTO(): ChangeOrderData
    {
        return new ChangeOrderData(
            projectId: (int) $this->validated('project_id'),
            description: $this->validated('description'),
            amountInCents: (int) round((float) $this->validated('amount') * 100),
            status: ChangeOrderStatus::from($this->validated('status')),
        );
    }
}
