<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\SupplierData;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Support\Facades\DB;

final class CreateSupplierAction
{
    public function execute(SupplierData $data, int $companyId): Supplier
    {
        return DB::transaction(function () use ($data, $companyId) {
            return Supplier::create([
                'company_id' => $companyId,
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address,
                'notes' => $data->notes,
            ]);
        });
    }
}
