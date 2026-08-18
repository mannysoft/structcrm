<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\Supplier;
use Illuminate\Support\Facades\DB;

final class DeleteSupplierAction
{
    public function execute(Supplier $supplier): void
    {
        DB::transaction(function () use ($supplier) {
            $supplier->delete();
        });
    }
}
