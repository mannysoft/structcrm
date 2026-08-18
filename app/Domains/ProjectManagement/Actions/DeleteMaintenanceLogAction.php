<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Illuminate\Support\Facades\DB;

final class DeleteMaintenanceLogAction
{
    public function execute(MaintenanceLog $log): void
    {
        DB::transaction(function () use ($log) {
            $log->delete();
        });
    }
}
