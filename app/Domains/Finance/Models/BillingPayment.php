<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPayment extends Model
{
    protected $table = 'billing_payments';

    protected $fillable = [
        'billing_id',
        'payment_date',
        'amount',
        'method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'immutable_date',
        'amount' => 'integer',
    ];

    /**
     * Get the billing that owns the payment.
     *
     * @return BelongsTo<Billing, $this>
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
}
