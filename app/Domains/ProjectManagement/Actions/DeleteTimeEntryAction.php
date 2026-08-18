<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\Models\TimeEntry;
use Illuminate\Support\Facades\DB;

final class DeleteTimeEntryAction
{
    public function execute(TimeEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            $entry->delete();
        });
    }
}
