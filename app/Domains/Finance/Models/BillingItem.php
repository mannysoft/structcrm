<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingItem extends Model
{
    protected $table = 'billing_items';

    protected $fillable = [
        'billing_id',
        'boq_item_id',
        'description',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Get the billing that owns the item.
     *
     * @return BelongsTo<Billing, $this>
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    /**
     * Get the BOQ item this billing item relates to.
     *
     * @return BelongsTo<BoqItem, $this>
     */
    public function boqItem(): BelongsTo
    {
        return $this->belongsTo(BoqItem::class);
    }
}
