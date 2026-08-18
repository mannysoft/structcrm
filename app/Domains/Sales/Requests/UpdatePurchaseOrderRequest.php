<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

class UpdatePurchaseOrderRequest extends StorePurchaseOrderRequest
{
    public function authorize(): bool
    {
        $purchaseOrder = $this->route('purchaseOrder');

        return $this->user()?->can('update', $purchaseOrder) ?? false;
    }
}
