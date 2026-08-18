<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\Models\Equipment;
use Illuminate\Support\Facades\DB;

final class DeleteEquipmentAction
{
    public function execute(Equipment $equipment): void
    {
        DB::transaction(function () use ($equipment) {
            $equipment->delete();
        });
    }
}
