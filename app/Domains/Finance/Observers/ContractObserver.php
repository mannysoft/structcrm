<?php

declare(strict_types=1);

namespace App\Domains\Finance\Observers;

use App\Domains\Finance\Models\Contract;

class ContractObserver
{
    public function creating(Contract $contract): void
    {
        if (empty($contract->contract_number)) {
            $contract->contract_number = $this->nextNumber($contract->company_id);
        }
    }

    private function nextNumber(?int $companyId): string
    {
        $count = Contract::withTrashed()
            ->where('company_id', $companyId)
            ->count();

        return sprintf('CON-%d', $count + 1);
    }
}
