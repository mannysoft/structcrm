<?php

declare(strict_types=1);

namespace App\Domains\Sales\Observers;

use App\Domains\Sales\Models\Estimate;

class EstimateObserver
{
    public function creating(Estimate $estimate): void
    {
        if (empty($estimate->estimate_number)) {
            $estimate->estimate_number = $this->nextNumber($estimate->company_id);
        }
    }

    private function nextNumber(?int $companyId): string
    {
        $count = Estimate::withTrashed()
            ->where('company_id', $companyId)
            ->count();

        return sprintf('EST-%d', $count + 1);
    }
}
