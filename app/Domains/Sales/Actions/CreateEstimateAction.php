<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\DTOs\EstimateData;
use App\Domains\Sales\Models\Estimate;
use Illuminate\Support\Facades\DB;

final class CreateEstimateAction
{
    public function execute(EstimateData $data, int $companyId): Estimate
    {
        return DB::transaction(function () use ($data, $companyId) {
            $estimate = Estimate::create([
                'company_id' => $companyId,
                'lead_id' => $data->leadId,
                'project_id' => $data->projectId,
                'title' => $data->title,
                'amount' => $data->amount->amountInCents,
                'currency' => $data->amount->currency,
                'status' => $data->status,
                'valid_until' => $data->validUntil,
            ]);

            return $estimate;
        });
    }
}
