<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

class UpdateChangeOrderRequest extends StoreChangeOrderRequest
{
    public function authorize(): bool
    {
        $changeOrder = $this->route('change_order');

        return $this->user()?->can('update', $changeOrder) ?? false;
    }
}
