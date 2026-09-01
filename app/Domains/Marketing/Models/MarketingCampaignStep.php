<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Models;

use Database\Factories\MarketingCampaignStepFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(MarketingCampaignStepFactory::class)]
class MarketingCampaignStep extends Model
{
    /** @use HasFactory<MarketingCampaignStepFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'campaign_id',
        'subject',
        'delay_days',
        'body',
        'sort_order',
    ];

    /**
     * The columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delay_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the campaign that owns the step.
     *
     * @return BelongsTo<MarketingCampaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }
}
