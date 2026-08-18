<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

class UpdateMaintenanceLogRequest extends StoreMaintenanceLogRequest
{
    public function authorize(): bool
    {
        $log = $this->route('maintenanceLog');

        return $this->user()?->can('update', $log) ?? false;
    }
}
