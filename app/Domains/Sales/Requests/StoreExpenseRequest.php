<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

use App\Common\ValueObjects\Money;
use App\Domains\Sales\DTOs\ExpenseData;
use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Expense;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Expense::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'receipt' => ['nullable', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): ExpenseData
    {
        return new ExpenseData(
            projectId: (int) $this->validated('project_id'),
            category: ExpenseCategory::from($this->validated('category')),
            amount: Money::fromDecimal((float) $this->validated('amount')),
            date: CarbonImmutable::parse($this->validated('date')),
            receiptPath: $this->storeReceipt(),
            notes: $this->validated('notes'),
        );
    }

    private function storeReceipt(): ?string
    {
        if (! $this->hasFile('receipt')) {
            return null;
        }

        return $this->file('receipt')->store('receipts');
    }
}
