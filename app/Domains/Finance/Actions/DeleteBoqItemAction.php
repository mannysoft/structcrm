<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\BoqItem;
use Illuminate\Support\Facades\DB;

final class DeleteBoqItemAction
{
    public function execute(BoqItem $boqItem): void
    {
        DB::transaction(function () use ($boqItem) {
            $boqItem->delete();
        });
    }
}
