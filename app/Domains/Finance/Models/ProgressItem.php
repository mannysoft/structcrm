<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressItem extends Model
{
    protected $table = 'progress_items';

    protected $fillable = [
        'progress_report_id',
        'boq_item_id',
        'cost_code_id',
        'description',
        'quantity',
        'percent_complete',
        'accomplished_amount',
    ];

    protected $casts = [
        'quantity' => 'float',
        'percent_complete' => 'float',
        'accomplished_amount' => 'integer',
    ];

    /**
     * Get the progress report that owns the item.
     *
     * @return BelongsTo<ProgressReport, $this>
     */
    public function progressReport(): BelongsTo
    {
        return $this->belongsTo(ProgressReport::class);
    }

    /**
     * Get the BOQ item this progress item relates to.
     *
     * @return BelongsTo<BoqItem, $this>
     */
    public function boqItem(): BelongsTo
    {
        return $this->belongsTo(BoqItem::class);
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
}
