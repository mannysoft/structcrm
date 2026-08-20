<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    protected $table = 'budget_items';

    protected $fillable = [
        'budget_id',
        'cost_code_id',
        'boq_item_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Get the budget that owns the item.
     *
     * @return BelongsTo<Budget, $this>
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the cost code assigned to the item.
     *
     * @return BelongsTo<CostCode, $this>
     */
    public function costCode(): BelongsTo
    {
        return $this->belongsTo(CostCode::class);
    }

    /**
     * Get the BOQ item this budget item originated from.
     *
     * @return BelongsTo<BoqItem, $this>
     */
    public function boqItem(): BelongsTo
    {
        return $this->belongsTo(BoqItem::class);
    }
}
