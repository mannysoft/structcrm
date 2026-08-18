<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\LeadData;
use App\Domains\Sales\Models\Lead;
use Illuminate\Support\Facades\DB;

final class CreateLeadAction
{
    public function execute(LeadData $data, int $companyId): Lead
    {
        return DB::transaction(function () use ($data, $companyId) {
            $lead = Lead::create([
                'company_id' => $companyId,
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
