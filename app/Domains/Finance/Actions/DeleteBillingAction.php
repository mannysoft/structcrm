<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\Billing;
use Illuminate\Support\Facades\DB;

final class DeleteBillingAction
{
    public function execute(Billing $billing): void
    {
        DB::transaction(function () use ($billing) {
            $billing->items()->delete();
            $billing->payments()->delete();
            $billing->delete();
        });
    }
}
