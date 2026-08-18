<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

use App\Domains\Sales\DTOs\InvoiceData;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Invoice;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Invoice::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function toDTO(): InvoiceData
    {
        return new InvoiceData(
            projectId: (int) $this->validated('project_id'),
            status: InvoiceStatus::from($this->validated('status')),
            issueDate: CarbonImmutable::parse($this->validated('issue_date')),
            dueDate: CarbonImmutable::parse($this->validated('due_date')),
            items: collect($this->validated('items'))->map(fn (array $item) => [
                'description' => $item['description'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (int) round((float) $item['unit_price'] * 100),
            ])->all(),
        );
    }
}
