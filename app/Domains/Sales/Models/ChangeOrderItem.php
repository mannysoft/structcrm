<?php

declare(strict_types=1);

namespace App\Domains\Sales\Models;

use App\Domains\Sales\Enums\ChangeOrderItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeOrderItem extends Model
{
    protected $table = 'change_order_items';

    protected $fillable = [
        'change_order_id',
        'category',
        'description',
        'amount',
    ];

    protected $casts = [
        'category' => ChangeOrderItemType::class,
        'amount' => 'integer',
    ];

    /**
     * Get the change order that owns the item.
     *
     * @return BelongsTo<ChangeOrder, $this>
     */
    public function changeOrder(): BelongsTo
    {
        return $this->belongsTo(ChangeOrder::class);
    }
}
