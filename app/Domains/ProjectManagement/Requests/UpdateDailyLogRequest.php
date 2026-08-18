<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

class UpdateDailyLogRequest extends StoreDailyLogRequest
{
    public function authorize(): bool
    {
        $dailyLog = $this->route('dailyLog');

        return $this->user()?->can('update', $dailyLog) ?? false;
    }
}
