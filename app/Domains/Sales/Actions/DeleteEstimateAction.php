<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\Estimate;
use Illuminate\Support\Facades\DB;

final class DeleteEstimateAction
{
    public function execute(Estimate $estimate): void
    {
        DB::transaction(function () use ($estimate) {
            $estimate->delete();
        });
    }
}
