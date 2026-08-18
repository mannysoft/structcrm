<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

class UpdateSupplierRequest extends StoreSupplierRequest
{
    public function authorize(): bool
    {
        $supplier = $this->route('supplier');

        return $this->user()?->can('update', $supplier) ?? false;
    }
}
