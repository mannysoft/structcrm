<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Models;

use Database\Factories\MarketingCampaignFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(MarketingCampaignFactory::class)]
class MarketingCampaign extends Model
{
    /** @use HasFactory<MarketingCampaignFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'is_active',
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
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the steps that belong to the campaign.
     *
     * @return HasMany<MarketingCampaignStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(MarketingCampaignStep::class, 'campaign_id')->orderBy('sort_order')->orderBy('id');
    }
}
