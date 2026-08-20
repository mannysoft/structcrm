<?php

declare(strict_types=1);

namespace App\Domains\Finance\Observers;

use App\Domains\Finance\Models\ProgressReport;

class ProgressReportObserver
{
    public function creating(ProgressReport $progressReport): void
    {
        if (empty($progressReport->report_number)) {
            $progressReport->report_number = $this->nextNumber($progressReport->company_id);
        }
    }

    private function nextNumber(?int $companyId): string
    {
        $count = ProgressReport::withTrashed()
            ->where('company_id', $companyId)
            ->count();

        return sprintf('REP-%d', $count + 1);
    }
}
