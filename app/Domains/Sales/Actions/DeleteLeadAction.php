<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\Lead;
use Illuminate\Support\Facades\DB;

final class DeleteLeadAction
{
    public function execute(Lead $lead): void
    {
        DB::transaction(function () use ($lead) {
            $lead->delete();
        });
    }
}
