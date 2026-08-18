<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\SupplierData;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Support\Facades\DB;

final class UpdateSupplierAction
{
    public function execute(Supplier $supplier, SupplierData $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {
            $supplier->update([
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address,
                'notes' => $data->notes,
            ]);

            return $supplier;
        });
    }
}
