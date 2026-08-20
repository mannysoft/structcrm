<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\ProgressReport;
use Illuminate\Support\Facades\DB;

final class DeleteProgressReportAction
{
    public function execute(ProgressReport $progressReport): void
    {
        DB::transaction(function () use ($progressReport) {
            $progressReport->items()->delete();
            $progressReport->delete();
        });
    }
}
