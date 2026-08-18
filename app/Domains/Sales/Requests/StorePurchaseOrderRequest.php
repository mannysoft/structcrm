<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

use App\Domains\Sales\DTOs\PurchaseOrderData;
use App\Domains\Sales\Enums\PurchaseOrderStatus;
use App\Domains\Sales\Models\PurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PurchaseOrder::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->where('company_id', $companyId)],
            'status' => ['required', Rule::enum(PurchaseOrderStatus::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function toDTO(): PurchaseOrderData
    {
        return new PurchaseOrderData(
            projectId: (int) $this->validated('project_id'),
            supplierId: (int) $this->validated('supplier_id'),
            status: PurchaseOrderStatus::from($this->validated('status')),
            items: collect($this->validated('items'))->map(fn (array $item) => [
                'description' => $item['description'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (int) round((float) $item['unit_price'] * 100),
            ])->all(),
        );
    }
}
