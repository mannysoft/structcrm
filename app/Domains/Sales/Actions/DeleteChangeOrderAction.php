<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Support\Facades\DB;

final class DeleteChangeOrderAction
{
    public function execute(ChangeOrder $changeOrder): void
    {
        DB::transaction(function () use ($changeOrder) {
            $changeOrder->delete();
        });
    }
}
