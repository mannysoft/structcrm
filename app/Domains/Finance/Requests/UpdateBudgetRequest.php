<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

class UpdateBudgetRequest extends StoreBudgetRequest
{
    public function authorize(): bool
    {
        $budget = $this->route('budget');

        return $this->user()?->can('update', $budget) ?? false;
    }
}
