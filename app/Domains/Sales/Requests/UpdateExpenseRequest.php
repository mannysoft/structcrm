<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

class UpdateExpenseRequest extends StoreExpenseRequest
{
    public function authorize(): bool
    {
        $expense = $this->route('expense');

        return $this->user()?->can('update', $expense) ?? false;
    }
}
