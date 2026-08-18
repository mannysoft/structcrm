<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

class UpdateLeadRequest extends StoreLeadRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $this->user()?->can('update', $lead) ?? false;
    }
}
