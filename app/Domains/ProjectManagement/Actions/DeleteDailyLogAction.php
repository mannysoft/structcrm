<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\Models\DailyLog;
use Illuminate\Support\Facades\DB;

final class DeleteDailyLogAction
{
    public function execute(DailyLog $dailyLog): void
    {
        DB::transaction(function () use ($dailyLog) {
            $dailyLog->delete();
        });
    }
}
