<?php

declare(strict_types=1);

namespace App\Domains\Finance\Observers;

use App\Domains\Finance\Models\Billing;

class BillingObserver
{
    public function creating(Billing $billing): void
    {
        if (empty($billing->billing_number)) {
            $billing->billing_number = $this->nextNumber($billing->company_id);
        }
    }

    private function nextNumber(?int $companyId): string
    {
        $count = Billing::withTrashed()
            ->where('company_id', $companyId)
            ->count();

        return sprintf('BIL-%d', $count + 1);
    }
}
