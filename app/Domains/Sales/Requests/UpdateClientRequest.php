<?php

declare(strict_types=1);

namespace App\Domains\Sales\Requests;

class UpdateClientRequest extends StoreClientRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $this->user()?->can('update', $client) ?? false;
    }
}
