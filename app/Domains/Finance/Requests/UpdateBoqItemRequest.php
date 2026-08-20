<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

class UpdateBoqItemRequest extends StoreBoqItemRequest
{
    public function authorize(): bool
    {
        $boqItem = $this->route('boq_item');

        return $this->user()?->can('update', $boqItem) ?? false;
    }
}
