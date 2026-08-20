<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\DTOs\ContractData;
use App\Domains\Finance\Models\Contract;
use Illuminate\Support\Facades\DB;

final class UpdateContractAction
{
    public function execute(Contract $contract, ContractData $data): Contract
    {
        return DB::transaction(function () use ($contract, $data) {
            $contract->update([
                'project_id' => $data->projectId,
                'title' => $data->title,
                'contract_value_amount' => $data->contractValue->amountInCents,
                'contract_value_currency' => $data->contractValue->currency,
                'status' => $data->status,
                'signed_at' => $data->signedAt,
            ]);

            return $contract;
        });
    }
}
