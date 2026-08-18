<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\LeadData;
use App\Domains\Sales\Models\Lead;
use Illuminate\Support\Facades\DB;

final class UpdateLeadAction
{
    public function execute(Lead $lead, LeadData $data): Lead
    {
        return DB::transaction(function () use ($lead, $data) {
            $lead->update([
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'email' => $data->email,
                'phone' => $data->phone,
                'status' => $data->status,
                'notes' => $data->notes,
            ]);

            return $lead;
        });
    }
}
