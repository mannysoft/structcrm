<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

class UpdateInvoiceRequest extends StoreInvoiceRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $this->user()?->can('update', $invoice) ?? false;
    }
}
