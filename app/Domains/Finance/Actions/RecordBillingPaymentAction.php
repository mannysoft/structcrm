<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class RecordBillingPaymentAction
{
    public function execute(
        Billing $billing,
        CarbonImmutable $date,
        int $amountInCents,
        ?string $method = null,
        ?string $reference = null,
        ?string $notes = null,
    ): Billing {
        return DB::transaction(function () use ($billing, $date, $amountInCents, $method, $reference, $notes) {
            $billing->payments()->create([
                'payment_date' => $date,
                'amount' => $amountInCents,
                'method' => $method,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            $billing->load('payments');

            $balance = $billing->net_amount - $billing->payments->sum('amount');

            $billing->forceFill([
                'status' => $balance <= 0 ? BillingStatus::Paid : BillingStatus::PartiallyPaid,
            ])->save();

            return $billing;
        });
    }
}
