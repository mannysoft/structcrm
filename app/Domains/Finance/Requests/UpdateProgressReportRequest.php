<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

class UpdateProgressReportRequest extends StoreProgressReportRequest
{
    public function authorize(): bool
    {
        $progressReport = $this->route('progress_report');

        return $this->user()?->can('update', $progressReport) ?? false;
    }
}
